<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use DateException;

interface SortieManagerInterface
{
    /**
     * Should enroll $plongeur for $sortie
     * @param Plongeur $plongeur
     * @param Sortie $sortie
     * @return void
     * @throws AlreadyExistsException if the $plongeur is already in the $sortie
     */
    function signUpFor(Plongeur $plongeur, Sortie $sortie): void;

    /**
     * @param Plongeur $plongeur
     * @param Sortie $sortie
     * @return void
     * @throws MemberNotFoundException when trying to remove a $plonger that is not enrolled for a $sortie
     * @throws NotAllowedException when $plonger is the DP
     */
    function quit(Plongeur $plongeur, Sortie $sortie): void;

    /**
     * @param Sortie $sortie
     * @return void
     * @throws DateException if the date has been passed
     */
    function processNew(Sortie $sortie): void;

    /**
     * @param Sortie $sortie
     * @return void
     * @throws NotAllowedException when $sortie has already happened
     */
    function cancel(Sortie $sortie): void;



}