<?php

namespace App\Service;

use App\Entity\Commentaire;
use App\Entity\Moniteur;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;

interface CommentaireManagerInterface
{

    /**
     * @param Sortie $sortie
     * @param Plongeur $plongeur
     * @param Moniteur $moniteur
     * @param string $message that will be displayed to keep track on diver's progress
     * @return Commentaire that has the same date as $sortie
     * @throws NotAllowedException if $moniteur hasn't supervised $plongeur during $sortie
     * @throws AlreadyExistsException if a message has already been written for that $sortie from this $moniteur already
     */
    function create(
        Sortie $sortie,
        Plongeur $plongeur,
        Moniteur  $moniteur,
        string $message
    ): Commentaire;


    function edit(Commentaire $commentaire, string $message): Commentaire;

}