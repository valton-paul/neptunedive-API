<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Entity\Palanquee;
use App\Entity\Sortie;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;

interface PalanqueeManagerInterface
{

    /**
     * @param Sortie $sortie
     * @return Palanquee
     * @throws NotAllowedException When creating a group from an event that has already happened
     */
    function create(Sortie $sortie): Palanquee;

    /**
     * @param Palanquee $palanquee
     * @return void
     * @throws NotAllowedException When removing a group from an event that has already happened
     */
    function remove(Palanquee $palanquee): void;

    /**
     * @param Plongeur $moniteur
     * @param Palanquee $palanquee
     * @return void
     * @throws MemberNotFoundException If $moniteur is not enrolled for the event
     * @throws NotAllowedException If $moniteur doesn't have the required level (level of each diver)
     */
    function assignAsSupervisor(Plongeur $moniteur, Palanquee $palanquee): void;

    /**
     * @param Plongeur $plongeur
     * @param Palanquee $palanquee
     * @return void
     * @throws MemberNotFoundException If $plongeur is not enrolled for the event
     * @throws NotAllowedException If $plongeur has a higher level than the current supervisor
     */
    function assignAsDiver(Plongeur $plongeur, Palanquee $palanquee): void;

    /**
     * @param Palanquee $palanquee
     * @return void
     * @throws MemberNotFoundException if there is no supervisor
     */
    function kickCurrentSupervisor(Palanquee $palanquee): void;

    /**
     * @param Plongeur $plongeur
     * @param Palanquee $palanquee
     * @return void
     * @throws MemberNotFoundException if $plongeur is not part of $palanquee
     */
    function kick(Plongeur $plongeur, Palanquee $palanquee): void;
}