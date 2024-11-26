<?php

namespace App\Controller;

use App\Entity\DemandeInscription;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Form\DemandeInscriptionType;
use App\Repository\DemandeInscriptionRepository;
use App\Service\InscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InscriptionController extends AbstractController
{

//    #[IsGranted('ROLE_USER')]
    #[Route('/creerDemandeInscription', name: 'creerDemandeInscription', methods: ['GET', 'POST'])]
    public function creerDemandeInscription(Request $request, InscriptionManager $inscriptionManager, EntityManagerInterface $entityManager): Response
    {
        $demande = new DemandeInscription();
        $form = $this->createForm(DemandeInscriptionType::class, $demande, [
            'action' => $this->generateUrl('creerDemandeInscription'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $inscriptionManager->processNewInscription($demande);
                $entityManager->persist($demande);
                $entityManager->flush();
            } catch (AlreadyExistsException $e) {
                $this->addFlash('warning', 'Une demande a déjà été créée avec cet email');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $this->redirectToRoute('accueil'); // TODO : rediriger vers "demande transmise"
        }

        return $this->render('inscription/demandeInscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'form' => $form
        ]);
    }

//    #[IsGranted('ROLE_ADMIN')]
    #[Route('/afficherDemandesInscription', name: 'afficherDemandesInscription', methods: ['GET'])]
    public function afficherDemandes(DemandeInscriptionRepository $demandeInscriptionRepository)
    {
        $demandesNonValidees = $demandeInscriptionRepository->findBy(['validee' => '0']);

        return $this->render('inscription/listeDemandesInscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'demandes' => $demandesNonValidees
        ]);
    }

//    #[IsGranted('ROLE_ADMIN')]
    #[Route('/validerDemandeInscription', name: 'validerDemandeInscription', methods: ['POST'])]
    public function validerDemandeInscription(Request $request, DemandeInscriptionRepository $demandeInscriptionRepository, InscriptionManager $inscriptionManager, EntityManagerInterface $entityManager): Response
    {
        $id = $request->request->get('id');
        $demandeInscription = $demandeInscriptionRepository->find($id);

        if (!$demandeInscription) {
            $this->addFlash('warning', 'La demande n\'existe pas');
            return $this->redirectToRoute('afficherDemandesInscription');
        }

        try {
            $inscriptionManager->validate($demandeInscription);
            $entityManager->persist($demandeInscription);
            $entityManager->flush();
        } catch (NotAllowedException $e) {
            $this->addFlash('warning', 'La demande est déjà validée');
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        return $this->redirectToRoute('afficherDemandesInscription');
    }

    #[Route('/creerCompte/{id}', name: 'creerCompte', methods: ['GET', 'POST'])]
    public function creerCompte(Request $request, DemandeInscriptionRepository $demandeInscriptionRepository, InscriptionManager $inscriptionManager, EntityManagerInterface $entityManager): Response
    {
        $id = $request->request->get('id');
        $demandeInscription = $demandeInscriptionRepository->find($id);

        if (!$demandeInscription) {
            $this->addFlash('warning', 'La demande n\'existe pas');
            return $this->redirectToRoute('afficherDemandesInscription');
        }

        try {
            $inscriptionManager->validate($demandeInscription);
            $entityManager->persist($demandeInscription);
            $entityManager->flush();
        } catch (NotAllowedException $e) {
            $this->addFlash('warning', 'La demande est déjà validée');
        } catch (\Exception $e) {
            $this->addFlash('warning', $e->getMessage());
        }

        return $this->redirectToRoute('afficherDemandesInscription');
    }
}
