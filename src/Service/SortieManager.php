<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\NotAllowedException;
use App\Service\SortieManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;

class SortieManager implements SortieManagerInterface
{

    public function __construct(private EntityManagerInterface $entityManager,
                                private Security $security)
    {
    }

    /**
     * @inheritDoc
     */
    function signUpFor(Plongeur $plongeur, Sortie $sortie): void
    {
        $sortie->addInscription($plongeur);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function quit(Plongeur $plongeur, Sortie $sortie): void
    {
        $sortie->removeInscription($plongeur);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function processNew(Sortie $sortie, FormInterface $form): void
    {

        if (!$this->security->isGranted('ROLE_MONITEUR')) {
            throw new NotAllowedException("Vous n'êtes pas autorisé à créer une sortie");
        }
        if ($form->get('dateSortie')->getData() < new \DateTime()) {
            throw new \DateException();
        }
        $sortie->setDateSortie($form->get('dateSortie')->getData());
        $sortie->setLieu($form->get('lieu')->getData());
        $sortie->setType($form->get('type')->getData());
        $sortie->setCreneau($form->get('creneau')->getData());
        $sortie->setDirecteurPlongee($this->security->getUser());
        $sortie->setSecuriteSurface($form->get('securiteSurface')->getData());
        $sortie->setPiloteBateau($form->get('piloteBateau')->getData());
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function processEdit(Sortie $sortie, FormInterface $form): void
    {
        if (!$this->security->isGranted('ROLE_MONITEUR')) {
            throw new NotAllowedException("Vous n'êtes pas autorisé à éditer une sortie");
        }
        $date = new \DateTime($form->get('date')->getData());
        if ($date < new \DateTime()) {
            throw new \DateException();
        }
        $sortie->setDateSortie($date);
        $sortie->setLieu($form->get('lieu')->getData());
        $sortie->setType($form->get('type')->getData());
        $sortie->setCreneau($form->get('creneau')->getData());
        $sortie->setDirecteurPlongee($this->security->getUser());
        $sortie->setSecuriteSurface($form->get('securiteSurface')->getData());
        $sortie->setBateau($form->get('bateau')->getData());
        $sortie->setPiloteBateau($form->get('piloteBateau')->getData());
        $newInscriptions = $form->get('inscriptions')->getData();
        $currentInscriptions = $sortie->getInscriptions()->toArray();
        foreach ($currentInscriptions as $plongeur) {
            if (!in_array($plongeur, $newInscriptions, true)) {
                $sortie->removeInscription($plongeur);
            }
        }
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function cancel(Sortie $sortie): void
    {
        if ($sortie->getDateSortie() < new \DateTime()) {
            throw new NotAllowedException("Vous ne pouvez pas annuler une sortie passée");
        }
        $this->entityManager->remove($sortie);
        $this->entityManager->flush();
    }


}