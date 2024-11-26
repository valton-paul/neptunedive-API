<?php

namespace App\Service;

use App\Entity\DemandeInscription;
use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Repository\DemandeInscriptionRepository;
use App\Exception\NotAllowedException;
use App\Exception\AlreadyExistsException;
use App\Repository\PlongeurRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class InscriptionManager implements InscriptionManagerInterface
{
    public function __construct(
        private DemandeInscriptionRepository $demandeInscriptionRepository,
        private UserPasswordHasherInterface $passwordHasher)
    {
    }

    private function checkIfEmailExistsAlready(DemandeInscription $demandeInscription): void
    {
        $email = $demandeInscription->getEmail();
        $demandeInscription = $this->demandeInscriptionRepository->findOneBy(['email' => $email]);
        if ($demandeInscription) {
            throw new AlreadyExistsException();
        }
    }

    public function processNewInscription(DemandeInscription $demandeInscription): void
    {
        $this->checkIfEmailExistsAlready($demandeInscription);
    }

    public function validate(DemandeInscription $demandeInscription): void
    {
        if (!$demandeInscription->isValidee()) {
            $demandeInscription->setValidee(true);
        } else {
            throw new NotAllowedException('La demande est déjà validée');
        }
    }

    private function chiffrerMotDePasse(Plongeur $plongeur, ?string $plainPassword): void
    {
        $hashed = $this->passwordHasher->hashPassword($plongeur, $plainPassword);
        $plongeur->setPassword($hashed);
    }

    public function createPlongeurFromDemandeInscription(DemandeInscription $demandeInscription, string $plainPassword): Plongeur
    {
        $newPlongeur = new Plongeur();

        $newPlongeur->setEmail($demandeInscription->getEmail());
        $newPlongeur->setNom($demandeInscription->getEmail());
        $newPlongeur->setPrenom($demandeInscription->getEmail());
        $newPlongeur->setFormation($demandeInscription->isFormation());
        $this->chiffrerMotDePasse($newPlongeur, $plainPassword);

        $newPlongeur->setMoniteur(false);
        $newPlongeur->setNiveauMoniteur(NiveauMoniteur::NONE);

        return $newPlongeur;
    }
}