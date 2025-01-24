<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use DateException;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @param FormInterface $form
     * @return void
     * @throws DateException if the date has been passed
     * @throws NotAllowedException if the user is not allowed to create a sortie
     * @throws \Exception
     */
    function processNew(Sortie $sortie, FormInterface $form): void;

    /**
     * @param Sortie $sortie
     * @param FormInterface $form
     * @return void
     * @throws DateException if the date has been passed
     * @throws NotAllowedException if the user is not allowed to edit the sortie
     * @throws \Exception
     */
    function processEdit(Sortie $sortie, FormInterface $form): void;


    /**
     * @param Sortie $sortie
     * @return void
     * @throws NotAllowedException when $sortie has already happened
     */
    function cancel(Sortie $sortie): void;

    /**
     * @param $username
     * @param $password
     * @param $idGroupe
     * @param $date
     * @return array
     * @throws Exception
     * @throws NotFoundException
     */
    function getSortiesSpond($username, $password, $idGroupe,  $date): array;

    /**
     * @param array $sortie
     * @param $idSpondSortie
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    function processNewFromArray(array $sortie, $idSpondSortie): void;

    /**
     * @param array|null $sorties
     * @param DateTime|null $date
     * @throws OptimisticLockException
     * @throws \DateMalformedStringException
     * @throws NotFoundException
     * @throws ORMException
     */
    function importAllSortie(array $sorties = null, DateTime $date = null): void;



    /**
     * @throws \DateMalformedStringException
     * @throws OptimisticLockException
     * @throws \DateInvalidOperationException
     * @throws NotFoundException
     * @throws ORMException
     */
    public function updateSpondUpdateDate(): void;

    /**
     * @param $interval
     * @throws NotFoundException
     */
    public function updateSpondUpdateInterval($interval): void;


}