<?php

namespace App\Service;

use App\Entity\InscriptionSortie;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\NotFoundException;
use App\Repository\InscriptionSortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaiementService
{
    public function __construct(
        private EntityManagerInterface             $entityManager,
        private InscriptionSortieRepository        $inscriptionSortieRepository,
        private HttpClientInterface                $httpClient,
        private RequestStack                       $requestStack,
        private HelloAssoTokenManager              $tokenManager,
        #[Autowire('%helloAssoApiUrl%')] private   $apiBaseUrl,
    )
    {
    }

    public function sentPaymentRequest(
        InscriptionSortie $inscriptionSortie
    ): array
    {
        $sortie = $inscriptionSortie->getSortie();
        $plongeur = $inscriptionSortie->getPlongeur();
        $currentRequest = $this->requestStack->getCurrentRequest();
        $frontURL = $currentRequest->headers->get('referer');
        $backUrl = $frontURL . '/sorties/' . $sortie->getId() . '/payment';
        $data = [
            "totalAmount" => (int)floor($sortie->getPrice() * 100),
            "initialAmount" => (int)floor($sortie->getPrice() * 100),
            "itemName" => "Inscription à la sortie " . $sortie->getId(),
            "backUrl" => $backUrl,
            "errorUrl" => $backUrl,
            "returnUrl" => $backUrl,
            "containsDonation" => false,
            "payer" => [
                "firstName" => $plongeur->getPrenom(),
                "lastName" => $plongeur->getNom()

            ]
        ];
        $token = $this->tokenManager->getToken();
        $response = $this->httpClient->request('POST', $this->getHelloAssoCheckoutUrl(), [
            'headers' => [
                'Authorization' => 'Bearer ' .$token,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la création du paiement');
        }

        return $response->toArray();
    }


    /**
     * This creates a payment for a diver to pay for a dive
     * @param InscriptionSortie $inscriptionSortie the inscription to create the payment for
     * @return array the payment data
     * @throws \Exception if the payment could not be created
     */
    private function createPaymentData(
        InscriptionSortie $inscriptionSortie
    )
    {
        $paymentData = $this->sentPaymentRequest($inscriptionSortie);
        $inscriptionSortie->setPaymentId($paymentData['id']);
        $this->entityManager->flush();
        return $paymentData;
    }

    public function getStateFromInscription(InscriptionSortie $inscriptionSortie): string
    {
        if ($inscriptionSortie->getPaymentId() === null) {
            return 'NotPaid';
        }
        $paymentData = $this->getPaymentData($inscriptionSortie->getPaymentId());
        try {
            return $this->getState($paymentData);
        } catch (\Exception $e) {
            return 'NotPaid';
        }
    }

    public function updateInscriptionFromState(InscriptionSortie $inscriptionSortie): void
    {
        $paymentData = $this->getPaymentData($inscriptionSortie->getPaymentId());
        if ($inscriptionSortie->getPaymentDate() === null) {
            $this->setPaymentDate($inscriptionSortie, $paymentData);
        }
    }

    private function getState(array $paymentData): string
    {
        return $paymentData['order']['payments'][0]['state'];
    }

    private function hasPaid(array $paymentData): bool
    {
        try {
            return $this->getState($paymentData) === 'Authorized';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @throws NotFoundException if the diver is not registered to the dive
     */
    public function getInscriptionSortie(Plongeur $plongeur, Sortie $sortie): InscriptionSortie
    {
        $inscription = $this->inscriptionSortieRepository->findOneBy([
            'plongeur' => $plongeur,
            'sortie' => $sortie,
        ]);
        if ($inscription === null) {
            throw new NotFoundException('Le plongeur n\'est pas inscrit à cette sortie');
        }
        return $inscription;
    }

    /**
     * This returns the payment link for a diver to pay for a dive
     * This will create a payment if the link has not been created yet
     * It updates automatically InscriptionSortie entity with the payment ID if it was not set
     * It also updates the payment date if the payment was successful
     *
     * @param InscriptionSortie $inscription
     * @return string the payment link
     * @throws AlreadyExistsException if the diver has already paid
     */
    public function getPaymentLink(InscriptionSortie $inscription): string
    {
        if ($inscription->getPaymentId() === null) {
            return $this->createPaymentData($inscription)['redirectUrl'];
        }
        $paymentData = $this->getPaymentData($inscription->getPaymentId());
        if ($this->hasPaid($paymentData)) {
            if ($inscription->getPaymentDate() === null) {
                $this->setPaymentDate($inscription, $paymentData);
            }
            throw new AlreadyExistsException('Le paiement a déjà été effectué');
        }
        return $paymentData['redirectUrl'];
    }

    private function setPaymentDate(InscriptionSortie $inscriptionSortie, array $paymentData): void
    {
        $date = new \DateTime($paymentData['order']['payments'][0]['date']);
        $inscriptionSortie->setPaymentDate($date);
        $this->entityManager->flush();
    }

    private function getPaymentData(string $paymentId): array
    {
        $token = $this->tokenManager->getToken();
        $response = $this->httpClient->request('GET', $this->getHelloAssoCheckoutUrl() . '/' . $paymentId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération du paiement');
        }
        return $response->toArray();
    }

    private function getHelloAssoCheckoutUrl(): string
    {
        $slug = 'neptunedive'; //TODO : get the slug from the organization
        return $this->apiBaseUrl . '/v5/organizations/'. $slug . '/checkout-intents';
    }
}