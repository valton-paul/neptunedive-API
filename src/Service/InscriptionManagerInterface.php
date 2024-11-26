<?php

namespace App\Service;

use App\Entity\DemandeInscription;
use App\Entity\Plongeur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;

interface InscriptionManagerInterface
{
    /**
     * @param DemandeInscription $demandeInscription
     * @return void
     */
    function processNewInscription(DemandeInscription $demandeInscription): void;

    /**
     * @param DemandeInscription $demandeInscription
     * @return void
     * @throws NotAllowedException When the inscription is already validated
     */
    function validate(DemandeInscription $demandeInscription): void;

    /**
     * @param DemandeInscription $demandeInscription
     * @return Plongeur
     * @throws AlreadyExistsException When the plongeur already exists
     * @throws NotAllowedException When the inscription is not validated
     */
    function createPlongeurFromDemandeInscription(DemandeInscription $demandeInscription, string $plainPassword): Plongeur;
}