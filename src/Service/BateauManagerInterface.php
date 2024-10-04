<?php

namespace App\Service;

use App\Entity\Bateau;
use App\Entity\Sortie;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;

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
     * @return mixed
     * @throws NotFoundException
     * TODO Que faire si un bateau est supprimé alors qu'il est utilisé dans des sorties
     */
    function delete(Bateau $bateau): void;

    function areAvailable(Sortie $sortie): bool;
}