<?php

namespace App\Service;

use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use Doctrine\ORM\EntityManagerInterface;

class PalanqueeManager implements PalanqueeManagerInterface
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @inheritDoc
     */
    function create(Sortie $sortie): Palanquee
    {
        if ($sortie->getDateSortie() < new \DateTime('now')) {
            throw new NotAllowedException("La sortie a déjà eu lieu");
        }
        $palanquee = new Palanquee();
        $palanquee->setSortie($sortie);
        $this->entityManager->persist($palanquee);
        $this->entityManager->flush();
        return $palanquee;
    }

    /**
     * @inheritDoc
     */
    function remove(Palanquee $palanquee): void
    {
        if ($palanquee->getSortie()->getDateSortie() < new \DateTime('now')) {
            throw new NotAllowedException("La sortie a déjà eu lieu");
        }
        $palanquee->setSortie(null);
        $this->entityManager->remove($palanquee);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function assignAsSupervisor(Plongeur $moniteur, Palanquee $palanquee): void
    {
        if (!$palanquee->getSortie()->getInscriptions()->contains($moniteur)){
            throw new MemberNotFoundException();
        }
        if (!$moniteur->isMoniteur()){
            throw new NotAllowedException('Le plongeur sélectionné n\'est pas un moniteur');
        }
        if ($palanquee->getAssigner()->contains($moniteur)){
            $this->kick($moniteur, $palanquee);
        }
        $palanquee->setEncadrant($moniteur);
        $this->entityManager->persist($palanquee);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function assignAsDiver(Plongeur $plongeur, Palanquee $palanquee): void
    {
        if (!$palanquee->getSortie()->getInscriptions()->contains($plongeur)){
            throw new MemberNotFoundException();
        }
        if ($palanquee->getEncadrant() === $plongeur){
            $this->kickCurrentSupervisor($palanquee);
        }
        $palanquee->addAssigner($plongeur);
        $this->entityManager->persist($palanquee);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function kickCurrentSupervisor(Palanquee $palanquee): void
    {
        if (!$palanquee->getEncadrant()){
            throw new MemberNotFoundException();
        }
        $palanquee->setEncadrant(null);
        $this->entityManager->persist($palanquee);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function kick(Plongeur $plongeur, Palanquee $palanquee): void
    {
        if (!$palanquee->getAssigner()->contains($plongeur)){
            throw new MemberNotFoundException();
        }
        $palanquee->removeAssigner($plongeur);
        $this->entityManager->persist($palanquee);
        $this->entityManager->flush();
    }
}