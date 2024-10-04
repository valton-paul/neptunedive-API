<?php

namespace App\Service;

use App\Entity\Bateau;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use Doctrine\Common\Collections\Collection;

interface BateauManagerInterface
{
    /**
     * @param string $immatriculation
     * @param int $nbPlace
     * @return Bateau
     * @throws NotAllowedException if $nbPLace is a negative number
     */
    function create(string $immatriculation, int $nbPlace): Bateau;

    /**
     * @param Bateau $bateau
     * @return void
     * @throws NotFoundException TODO Que faire si un bateau est supprimé alors qu'il est utilisé dans des sorties
     */
    function delete(Bateau $bateau): void;

    /**
     * @param \DateTime $date
     * @param Creneaux $creneaux
     * @return Collection
     */
    function areAvailable(\DateTime $date, Creneaux $creneaux): Collection;
}