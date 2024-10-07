<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\PlongeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurManager implements UtilisateurManagerInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private PlongeurRepository $plongeurRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    private
    function chiffrerMotDePasse(Plongeur $plongeur, ?string $plainPassword): void
    {
        $hashed = $this->passwordHasher->hashPassword($plongeur, $plainPassword);
        $plongeur->setPassword($hashed);
    }

    private
    function checkIfEmailExistsAlready(Plongeur $plongeur): void
    {
        $email = $plongeur->getEmail();
        $plongeur = $this->plongeurRepository->findOneBy(['email' => $email]);
        if ($plongeur) {
            throw new \Exception('Cet email existe déjà');
        }
    }

    function processNewUtilisateur(Plongeur $plongeur, ?string $plainPassword): void
    {
        $this->checkIfEmailExistsAlready($plongeur);
        $this->chiffrerMotDePasse($plongeur, $plainPassword);
        $plongeur->setNiveauMoniteur(NiveauMoniteur::NONE);
        $plongeur->setMoniteur(false);
    }

    /**
     * @param Plongeur $plongeur
     * @return Plongeur
     * @throws NotAllowedException If $plongeur diving level is below N2
     * @throws AlreadyExistsException If $plongeur is already a supervisor
     */
    function makeSupervisor(Plongeur $plongeur): Plongeur
    {
        //TODO : a faire
    }
}