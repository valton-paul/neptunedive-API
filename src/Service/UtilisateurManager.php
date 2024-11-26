<?php

namespace App\Service;

use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\PlongeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurManager implements UtilisateurManagerInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $plongeurRepository;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private DatabaseContextResolver $resolver
    )
    {
        $this->entityManager = $this->resolver->getEntityManager();
        $this->plongeurRepository = $this->resolver->getRepository(Plongeur::class);
    }

    public
    function chiffrerMotDePasse(Plongeur $plongeur, ?string $plainPassword): void
    {
        $hashed = $this->passwordHasher->hashPassword($plongeur, $plainPassword);
        $plongeur->setPassword($hashed);
    }

    /**
     * @throws AlreadyExistsException
     */
    private
    function checkIfEmailExistsAlready(Plongeur $plongeur): void
    {
        $email = $plongeur->getEmail();
        $plongeur = $this->plongeurRepository->findOneBy(['email' => $email]);
        if ($plongeur) {
            throw new AlreadyExistsException('Cet email existe déjà');
        }
    }

    /**
     * @throws AlreadyExistsException
     */
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
    function makeSupervisor(Plongeur $plongeur, NiveauMoniteur $niveauMoniteur): Plongeur
    {
        $plongeur->setMoniteur(true);
        $plongeur->setNiveauMoniteur($niveauMoniteur);
        $this->entityManager->flush();
        return $plongeur;
    }

    function revokeSupervisor(Plongeur $plongeur): Plongeur
    {
        $plongeur->setMoniteur(false);
        $this->entityManager->flush();
        return $plongeur;
    }
}