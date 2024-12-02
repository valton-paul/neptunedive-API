<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\AlreadyExistsException;
use App\Exception\NotFoundException;
use App\Repository\SortieRepository;
use App\Service\PaiementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HelloAssoController extends AbstractController
{
    public function __construct(
        private PaiementService  $paiementService,
        private SortieRepository $sortieRepository
    )
    {
    }

    #[Route('/payment/{idSortie}', name: 'paymentLink', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function paymentLinkSortie(string $idSortie): Response
    {
        $sortie = $this->sortieRepository->find($idSortie);
        try {
            $inscription = $this->paiementService->getInscriptionSortie($this->getUser(), $sortie);
            $link = $this->paiementService->getPaymentLink($inscription);
        }  catch (NotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (AlreadyExistsException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse(["url" => $link]);
    }

    #[Route('/payment/{idSortie}/status', name: 'paymentStatus', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function paymentStatus(string $idSortie): Response
    {
        try {
            $inscription = $this->paiementService->getInscriptionSortie($this->getUser(), $this->sortieRepository->find($idSortie));
            $status = $this->paiementService->getStateFromInscription($inscription);
        }  catch (NotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse(["status" => $status]);
    }

    #[Route('/payment/{idSortie}/status', name: 'updatePaymentStatus', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePaymentStatus(string $idSortie): Response
    {
        try {
            $sortie = $this->sortieRepository->find($idSortie);
            if ($sortie === null) {
                throw new NotFoundException("Sortie not found");
            }
            $inscription = $this->paiementService->getInscriptionSortie($this->getUser(), $sortie);
            $this->paiementService->updateInscriptionFromState($inscription);
        }  catch (NotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse("", Response::HTTP_NO_CONTENT);
    }
}
