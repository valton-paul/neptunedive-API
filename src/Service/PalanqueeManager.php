<?php

namespace App\Service;

use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
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
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    function remove(Palanquee $palanquee): void
    {
        // TODO: Implement remove() method.
    }

    /**
     * @inheritDoc
     */
    function assignAsSupervisor(Plongeur $moniteur, Palanquee $palanquee): void
    {
        // TODO: Implement assignAsSupervisor() method.
    }

    /**
     * @inheritDoc
     */
    function assignAsDiver(Plongeur $plongeur, Palanquee $palanquee): void
    {
        // TODO: Implement assignAsDiver() method.
    }

    /**
     * @inheritDoc
     */
    function kickCurrentSupervisor(Palanquee $palanquee): void
    {
        // TODO: Implement kickCurrentSupervisor() method.
    }

    /**
     * @inheritDoc
     */
    function kick(Plongeur $plongeur, Palanquee $palanquee): void
    {
        // TODO: Implement kick() method.
    }
}