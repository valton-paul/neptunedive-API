<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Service\SortieManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;

class SortieManager implements SortieManagerInterface
{
    private $entityManager;

    public function __construct(
        private DatabaseContextResolver $provider,
        private Security                $security)
    {
        $this->entityManager = $this->provider->getEntityManager();
    }

    /**
     * @inheritDoc
     */
    function signUpFor(Plongeur $plongeur, Sortie $sortie): void
    {
        if ($sortie->getInscriptions()->contains($plongeur)) {
            throw new AlreadyExistsException();
        }
        $sortie->addInscription($plongeur);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function quit(Plongeur $plongeur, Sortie $sortie): void
    {
        if (!$sortie->getInscriptions()->contains($plongeur)) {
            throw new MemberNotFoundException();
        }
        if ($sortie->getDirecteurPlongee() === $plongeur) {
            throw new NotAllowedException("Vous ne pouvez pas vous désinscrire d'une sortie dont vous êtes le directeur de plongée");
        }
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
        $this->UpdateSortie($form, $sortie, $this->security->getUser());
        $plongeur = $this->security->getUser();
        if ($plongeur instanceof Plongeur) {
            $sortie->addInscription($plongeur);
        }
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
        $this->UpdateSortie($form, $sortie, $form->get('directeurPlongee')->getData());
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function cancel(Sortie $sortie): void
    {
        if ($sortie->getDateSortie() < new \DateTime()) {
            throw new NotAllowedException("Vous ne pouvez pas annuler une sortie    passée");
        }
        $this->entityManager->remove($sortie);
        $this->entityManager->flush();
    }

    /**
     * @param FormInterface $form
     * @param Sortie $sortie
     * @return void
     * @throws \DateException
     */
    private function UpdateSortie(FormInterface $form, Sortie $sortie, $directeurPlongee): void
    {
        $date = $form->get('dateSortie')->getData();
        if ($date < new \DateTime()) {
            throw new \DateException();
        }
        $sortie->setDateSortie($date);
        $sortie->setLieu($form->get('lieu')->getData());
        $sortie->setType($form->get('type')->getData());
        $sortie->setCreneau($form->get('creneau')->getData());
        $sortie->setDirecteurPlongee($directeurPlongee);
        $sortie->setSecuriteSurface($form->get('securiteSurface')->getData());
        $sortie->setPiloteBateau($form->get('piloteBateau')->getData());
        /*$sortie->setBateau($form->get('bateau')->getData());*/
    }


}