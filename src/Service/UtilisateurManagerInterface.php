<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;

interface UtilisateurManagerInterface
{
    function processNewUtilisateur(Plongeur $plongeur, ?string $plainPassword) : void;

    /**
     * @param Plongeur $plongeur
     * @return Plongeur
     * @throws NotAllowedException If $plongeur diving level is below N2
     * @throws AlreadyExistsException If $plongeur is already a supervisor
     */
    function makeSupervisor(Plongeur $plongeur, NiveauMoniteur $niveauMoniteur): Plongeur;
    function revokeSupervisor(Plongeur $plongeur): Plongeur;

    function getPlongeurByLastnameAndFirstname(string $nom, string $prenom): Plongeur|null;
}