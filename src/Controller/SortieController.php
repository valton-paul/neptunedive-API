<?php

namespace App\Controller;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Form\Sortie\SortieCreateType;
use App\Form\Sortie\SortieEditType;
use App\Service\BateauManager;
use App\Service\BateauManagerInterface;
use App\Service\DatabaseContextResolver;
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
    public function __construct(private BateauManagerInterface $bateauManager){}

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{id}/signup', name: 'signUpToSortie', methods: ['GET'])]
    public function signUp(int $id, SortieManagerInterface $sortieManager, DatabaseContextResolver $resolver): Response
    {
        $sortie = $resolver->getRepository(Sortie::class)->find($id);
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
    #[Route('/sortie/create', name: 'createSortie', methods: ['GET', 'POST'])]
    public function createSortie(SortieManagerInterface $sortieManager, Request $request, DatabaseContextResolver $databaseContextResolver): Response
    {
        $sortie = new Sortie();

        // Récupérer les entités Plongeur et Bateau
        $moniteurs = $databaseContextResolver->getRepository(Plongeur::class)->findBy(['isMoniteur' => 1]);
        $bateaux = $databaseContextResolver->getRepository(Bateau::class)->findAll();

        // Créer le formulaire en passant les moniteurs et bateaux
        $form = $this->createForm(SortieCreateType::class, $sortie, [
            'moniteurs' => $moniteurs,
            'bateaux' => $bateaux,
            'method' => 'POST',
            'action' => $this->generateURL('createSortie')
        ]);

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $sortieManager->processNew($sortie, $form);
                return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return new JsonResponse($e->getMessage());
            }
        }

        // Afficher le formulaire
        return $this->render('sortie/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/sortie/{id}/quit/{idPlongeur}', name: 'quitSortie', methods: ['GET'])]
    public function quit(int $id, $idPlongeur, SortieManagerInterface $sortieManager, DatabaseContextResolver $resolver): Response
    {
        $sortie = $resolver->getRepository(Sortie::class)->find($id);
        $plongeur = $resolver->getRepository(Plongeur::class)->find($idPlongeur);
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
        } else {
            $this->addFlash('success', 'Plongeur retiré de la sortie');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted('ROLE_MONITEUR')]
    #[Route('/sortie/{id}/cancel', name: 'cancelSortie', methods: ['GET'])]
    public function cancel(int $id, SortieManagerInterface $sortieManager, DatabaseContextResolver $resolver): Response
    {
        $sortie = $resolver->getRepository(Sortie::class)->find($id);
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

    #[Route('/sortie/{id}', name: 'sortie', methods: ['GET', 'POST'])]
    public function edit(int                     $id,
                         SortieManagerInterface  $sortieManager,
                         Request                 $request,
                         DatabaseContextResolver $resolver): Response
    {
        $sortie = $resolver->getRepository(Sortie::class)->find($id);
        if ($sortie == null) {
            $this->addFlash('error', 'Sortie inconnue');
            return $this->redirectToRoute('accueil');
        }

        // Récupérer les moniteurs et bateaux disponibles
        $moniteurs = $resolver->getRepository(Plongeur::class)->findBy(['isMoniteur' => 1]);
        $availableBoats = $this->bateauManager->areAvailable($sortie->getDateSortie(), $sortie->getCreneau());
        $availableBoats[] = $sortie->getBateau();
        dump(count($availableBoats));
        // Créer le formulaire en passant les entités via les options
        $form = $this->createForm(SortieEditType::class, $sortie, [
            'moniteurs' => $moniteurs,
            'bateaux' => $availableBoats,
            'method' => 'POST',
            'action' => $this->generateURL('sortie', ['id' => $sortie->getId()]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
            'form' => $form->createView(),
            'sortie' => $sortie,
            'isInscrit' => $isInscrit
        ]);
    }

    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function acceuil(DatabaseContextResolver $resolver): Response
    {
        $sorties = $resolver->getRepository(Sortie::class)->findAll();

        return $this->render('accueil.html.twig', [
            'sorties' => $sorties
        ]);
    }

}