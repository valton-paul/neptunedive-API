<?php

namespace App\Service;

use App\Entity\InscriptionPlongeur;
use App\Entity\Plongeur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;

interface InscriptionPlongeurManagerInterface
{
    /**
     * @param InscriptionPlongeur $inscriptionPlongeur
     * @return void
     */
    function processNewInscription(InscriptionPlongeur $inscriptionPlongeur): void;

    /**
     * @param InscriptionPlongeur $inscriptionPlongeur
     * @return void
     * @throws NotAllowedException si la demande a déjà été validée
     */
    function validateInscription(InscriptionPlongeur $inscriptionPlongeur): void;

    /**
     * @param InscriptionPlongeur $inscriptionPlongeur
     * @param string $plainPassword
     * @return Plongeur nouvellement crée
     * @throws NotAllowedException si la demande n'a pas été validée
     * @throws AlreadyExistsException si le plongeur existe déjà
     */
    function createPlongeurFromDemande(InscriptionPlongeur $inscriptionPlongeur, string $plainPassword): Plongeur;
}