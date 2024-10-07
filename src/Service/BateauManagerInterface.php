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
     * @param Bateau $bateau
     * @param string|null $newImmatriculation
     * @param int|null $newNbPlace
     * @return Bateau
     * @throws NotFoundException if the boats doesn't exist
     * @throws NotAllowedException if the $newNbPlace is null or < 0
     */
    function update(Bateau $bateau, int $newNbPlace = null, string $newImmatriculation = null): Bateau;
    /**
     * @param \DateTime $date
     * @param Creneaux $creneaux
     * @return array
     */
    function areAvailable(\DateTime $date, Creneaux $creneaux): array;
}