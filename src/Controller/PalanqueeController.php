<?php

namespace App\Controller;

use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Repository\PlongeurRepository;
use App\Service\PalanqueeManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PalanqueeController extends AbstractController
{

    public function __construct(
        private PalanqueeManagerInterface $palanqueeManager,
        private EntityManagerInterface $entityManager
    )
    {

    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getDirecteurPlongee().getId() == user.getId()"),
        subject: "sortie")]
    #[Route('/sortie/{id}/createPalanquee', name: 'createPalanquee', methods: ['GET'])]
    public function createPalanquee(Sortie $sortie): Response
    {
        try {
            $this->palanqueeManager->create($sortie);
        }catch (NotAllowedException $e){
            $this->addFlash('error', 'La sortie a laquelle vous souhaitez ajouté une palanquee a déjà eu lieu');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getSortie().getDirecteurPlongee().getId() == user.getId()"),
        subject: "palanquee")]
    #[Route('/sortie/removePalanquee/{id}', name: 'removePalanquee', methods: ['GET'])]
    public function removePalanquee(Palanquee $palanquee): Response
    {
        $sortie = $palanquee->getSortie();
        try {
            $this->palanqueeManager->remove($palanquee);
        } catch (NotAllowedException $e){
            $this->addFlash('error', 'La sortie a laquelle vous souhaitez enlevez une palanquee a déjà eu lieu');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getSortie().getDirecteurPlongee().getId() == user.getId()"),
        subject: "palanquee")]
    #[Route('/sortie/palanquee/{id}/assignSupervisor/{idMoniteur}', name: 'assignAsSupervisor', methods: ['GET'])]
    public function assignAsSupervisor(Palanquee $palanquee, $idMoniteur) : Response
    {
        $moniteur = $this->entityManager->getRepository(Plongeur::class)->find($idMoniteur);
        $sortie = $palanquee->getSortie();
        if ($moniteur == null){
            $this->addFlash('error', 'Le moniteur n\'existe pas');
            return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
        }
        try {
            $this->palanqueeManager->assignAsSupervisor($moniteur, $palanquee);
        } catch (NotAllowedException $e){
            $this->addFlash('error', $e->getMessage());
        } catch (MemberNotFoundException $e){
            $this->addFlash('error', 'Le moniteur sélectionné n\'est pas inscrit a la sortie dont la palanquee fait partie');
        }

        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getSortie().getDirecteurPlongee().getId() == user.getId()"),
        subject: "palanquee")]
    #[Route('/sortie/palanquee/{id}/assign/{idPlongeur}', name: 'assignAsDiver', methods: ['GET'])]
    public function assignAsDiver(Palanquee $palanquee, $idPlongeur) : Response
    {
        $plongeur = $this->entityManager->getRepository(Plongeur::class)->find($idPlongeur);
        $sortie = $palanquee->getSortie();
        if ($plongeur == null){
            $this->addFlash('error', 'Le plongeur n\'existe pas');
            return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
        }
        try {
            $this->palanqueeManager->assignAsDiver($plongeur, $palanquee);
        } catch (MemberNotFoundException $e){
            $this->addFlash('error', 'Le plongeur sélectionné n\'est pas inscrit a la sortie dont la palanquee fait partie');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getSortie().getDirecteurPlongee().getId() == user.getId()"),
        subject: "palanquee")]
    #[Route('/sortie/palanquee/{id}/kickSupervisor', name: 'kickSupervisor', methods: ['GET'])]
    public function kickSupervisor(Palanquee $palanquee): Response
    {
        $sortie = $palanquee->getSortie();
        try {
            $this->palanqueeManager->kickCurrentSupervisor($palanquee);
        } catch (NotAllowedException $e){
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

    #[IsGranted(attribute: new Expression("is_granted('ROLE_MONITEUR') && subject.getSortie().getDirecteurPlongee().getId() == user.getId()"),
        subject: "palanquee")]
    #[Route('/sortie/palanquee/{id}/kick/{idPlongeur}', name: 'kickDiver', methods: ['GET'])]
    public function kickDiver(Palanquee $palanquee, $idPlongeur): Response
    {
        $plongeur = $this->entityManager->getRepository(Plongeur::class)->find($idPlongeur);
        $sortie = $palanquee->getSortie();
        try {
            $this->palanqueeManager->kick($plongeur, $palanquee);
        } catch (MemberNotFoundException $e){
            $this->addFlash('error', 'Le plongeur selectionné ne fait pas partie de la palanquee');
        }
        return $this->redirectToRoute('sortie', ['id' => $sortie->getId()]);
    }

}
