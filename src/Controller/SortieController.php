<?php

namespace App\Controller;

use App\Entity\Moniteur;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Form\Sortie\SortieCreateType;
use App\Form\Sortie\SortieEditType;
use App\Service\SortieManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class SortieController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{idSortie}/signup', name: 'signUpToSortie', methods: ['GET'])]
    public function signUp(Sortie $sortie, SortieManagerInterface $sortieManager): Response
    {
        try {
            $plongeur = $this->getUser();
            if ($plongeur instanceof Plongeur) {
                $sortieManager->signUpFor($plongeur, $sortie);
            }
        } catch (AlreadyExistsException $e) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie');
        }
        return new JsonResponse(['status' => 'ok']);
    }


    #[IsGranted('ROLE_MONITEUR')]
    #[Route('/sortie/create', name: 'createSortie', methods: ['GET','POST'])]
    public function createSortie(SortieManagerInterface $sortieManager,Request $request): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieCreateType::class, $sortie, ['method' => 'POST',
            'action' => $this->generateURL('createSortie')]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            try {

                $sortieManager->processNew($sortie, $form);
            } catch (\DateException $e) {
                return new JsonResponse(['status' => 'error', 'message' => 'La date de la sortie est passée']);
            } catch (NotAllowedException $e) {
                return new JsonResponse(['status' => 'error', 'message' => 'Vous n\'êtes pas autorisé à créer une sortie']);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return new JsonResponse(['status' => 'ok']);
        }

        return $this->render('sortie/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{idSortie}/quit', name: 'quitSortie', methods: ['GET'])]
    public function quit(Sortie $sortie, SortieManagerInterface $sortieManager): Response
    {
        $plongeur = $this->getUser();
        if ($plongeur instanceof Plongeur) {
            try {
                $sortieManager->quit($plongeur, $sortie);
            } catch (NotAllowedException $e) {
                $this->addFlash('warning', 'Vous êtes le directeur de plongée de cette sortie');
            } catch (MemberNotFoundException $e) {
                $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie');
            }
        }
        return new JsonResponse(['status' => 'ok']);
    }

    #[IsGranted('ROLE_MONITEUR')]
    #[Route('/sortie/{idSortie}/cancel', name: 'cancelSortie', methods: ['GET'])]
    public function cancel(Sortie $sortie, SortieManagerInterface $sortieManager): Response
    {
        try {
            $sortieManager->cancel($sortie);
        } catch (NotAllowedException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return new JsonResponse(['status' => 'ok']);
    }

    #[IsGranted('ROLE_MONITEUR')]
    #[Route('/sortie/{idSortie}/edit', name: 'editSortie', methods: ['GET','POST'])]
    public function edit(Sortie $sortie, SortieManagerInterface $sortieManager, Request $request): Response
    {
        $form = $this->createForm(SortieEditType::class, $sortie, ['method' => 'POST',
            'action' => $this->generateURL('editSortie', ['idSortie' => $sortie->getId()])]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            try {
                $sortieManager->processNew($sortie, $form);
            } catch (\DateException $e) {
                $this->addFlash('warning', 'La date de la sortie est passée');
            } catch (NotAllowedException $e) {
                $this->addFlash('warning', 'Vous n\'êtes pas autorisé à créer une sortie');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Une erreur est survenue');
            }
            return $this->redirectToRoute('editSortie', ['idSortie' => $sortie->getId()]);
        }

        return $this->render('sortie/manage.html.twig', [
            'form' => $form,
        ]);
    }

}