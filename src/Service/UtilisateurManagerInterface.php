<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;

interface UtilisateurManagerInterface
{
    function processNewUtilisateur(Plongeur $utilisateur, ?string $plainPassword) : void;

    /**
     * @param Plongeur $plongeur
     * @return Plongeur
     * @throws NotAllowedException If $plongeur diving level is below N2
     * @throws AlreadyExistsException If $plongeur is already a supervisor
     */
    function makeSupervisor(Plongeur $plongeur): Plongeur;
}