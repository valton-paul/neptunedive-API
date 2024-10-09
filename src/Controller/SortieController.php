<?php

namespace App\Controller;

use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Form\Sortie\SortieCreateType;
use App\Form\Sortie\SortieEditType;
use App\Service\BateauManagerInterface;
use App\Service\SortieManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class SortieController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{id}/signup', name: 'signUpToSortie', methods: ['GET'])]
    public function signUp(?Sortie $sortie, SortieManagerInterface $sortieManager, EntityManagerInterface $entityManager): Response
    {
        if ($sortie == null) {
            $this->addFlash('error', 'Sortie inconnue');
            return $this->redirectToRoute('accueil');
        }
        $plongeur = $this->getUser();
        try {
            if ($plongeur instanceof Plongeur) {
                $sortieManager->signUpFor($plongeur, $sortie);
            }
        } catch (AlreadyExistsException $e) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
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
                $this->addFlash('warning', 'La date de la sortie est passée');
                return $this->redirectToRoute('createSortie');
            } catch (NotAllowedException $e) {
                $this->addFlash('warning', 'Vous n\'êtes pas autorisé à créer une sortie');
                return $this->redirectToRoute('createSortie');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('createSortie');
            }
            return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
        }
        return $this->render('sortie/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{id}/quit/{idPlongeur}', name: 'quitSortie', methods: ['GET'])]
    public function quit(?Sortie $sortie,$idPlongeur, SortieManagerInterface $sortieManager, EntityManagerInterface $entityManager): Response
    {
        $plongeur = $entityManager->getRepository(Plongeur::class)->find($idPlongeur);
        if ($sortie == null) {
            $this->addFlash('error', 'Sortie inconnue');
            return $this->redirectToRoute('accueil');
        }
        if ($plongeur == null) {
            $this->addFlash('error', 'Plongeur inconnu');
            return $this->redirectToRoute('accueil');
        }
        try {
            $sortieManager->quit($plongeur, $sortie);
        } catch (NotAllowedException $e) {
            $this->addFlash('warning', 'Vous êtes le directeur de plongée de cette sortie');
            return $this->redirectToRoute('accueil');
        } catch (MemberNotFoundException $e) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie');
            return $this->redirectToRoute('accueil');
        }
        if ($plongeur == $this->getUser()) {
            $this->addFlash('success', 'Vous avez quitté la sortie');
        }else{
            $this->addFlash('success', 'Plongeur retiré de la sortie');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted('ROLE_MONITEUR')]
    #[Route('/sortie/{id}/cancel', name: 'cancelSortie', methods: ['GET'])]
    public function cancel(?Sortie $sortie, SortieManagerInterface $sortieManager, EntityManagerInterface $entityManager): Response
    {
        if ($sortie == null) {
            $this->addFlash('error', 'Sortie inconnue');
            return $this->redirectToRoute('accueil');
        }
        try {
            $sortieManager->cancel($sortie);
            $this->addFlash('success', 'Sortie annulée');
        } catch (NotAllowedException $e) {
            $this->addFlash('warning', $e->getMessage());
        }
        return $this->redirectToRoute('accueil');
    }

    #[Route('/sortie/{id}', name: 'sortie', methods: ['GET','POST'])]
    public function edit(?Sortie $sortie,
                         SortieManagerInterface $sortieManager,
                         Request $request,
                         BateauManagerInterface $bateauManager): Response
    {
        if ($sortie == null) {
            $this->addFlash('error', 'Sortie inconnue');
            return $this->redirectToRoute('accueil');
        }
        $availableBoats = $bateauManager->areAvailable($sortie->getDateSortie(), $sortie->getCreneau());
        if ($sortie->getBateau()) {
            $currentBoat = $sortie->getBateau();
            $boats = [$currentBoat];
            foreach ($availableBoats as $boat) {
                if ($boat !== $currentBoat) {
                    $boats[] = $boat;
                }
            }
        } else {
            $boats = $availableBoats;
        }
        $form = $this->createForm(SortieEditType::class, $sortie,
            [
                'method' => 'POST',
                'action' => $this->generateURL('sortie', ['id' => $sortie->getId()]),
                'sortie' => $sortie,
                'boats' => $boats
            ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            try {
                $sortieManager->processEdit($sortie, $form);
            } catch (\DateException $e) {
                $this->addFlash('warning', 'La date de la sortie est passée');
            } catch (NotAllowedException $e) {
                $this->addFlash('warning', 'Vous n\'êtes pas autorisé à créer une sortie');
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
            }
            return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
        }

        $isInscrit = $sortie->getInscriptions()->contains($this->getUser());

        return $this->render('sortie/manage.html.twig', [
            'form' => $form,
            'sortie' => $sortie,
            'isInscrit' => $isInscrit
        ]);
    }

    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function acceuil(EntityManagerInterface $entityManager): Response
    {
        $sorties = $entityManager->getRepository(Sortie::class)->findAll();

        return $this->render('accueil.html.twig', [
            'sorties' => $sorties
        ]);
    }

}