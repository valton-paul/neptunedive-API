<?php

namespace App\DTO;

use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use Symfony\Component\Validator\Constraints as Assert;

class CreateInscriptionInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $nom = null;

    #[Assert\NotBlank]
    public ?string $prenom = null;

    #[Assert\Type("bool")]
    public ?bool $formation = null;

    public ?NiveauPlongeeAutonome $niveauPlongeeAutonome = null;
    public ?NiveauPlongeeEncadre $niveauPlongeeEncadre = null;
    public ?NiveauMoniteur $niveauMoniteur = null;

    public $photoDeProfil = null;
    public $carteNiveau = null;
    public $certificatMedical = null;

    // Setter pour "formation"
    public function setFormation(string $formation): void
    {
        $this->formation = match (strtolower($formation)) {
            "true" => true,
            "false" => false,
            default => null,
        };
    }

    // Setters pour les autres champs
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function setNiveauPlongeeAutonome(?string $niveauPlongeeAutonome): void
    {
        $this->niveauPlongeeAutonome = NiveauPlongeeAutonome::tryFrom((int)$niveauPlongeeAutonome);
    }

    public function setNiveauPlongeeEncadre(?string $niveauPlongeeEncadre): void
    {
        $this->niveauPlongeeEncadre = NiveauPlongeeEncadre::tryFrom((int)$niveauPlongeeEncadre);
    }

    public function setNiveauMoniteur(?string $niveauMoniteur): void
    {
        $this->niveauMoniteur = NiveauMoniteur::tryFrom($niveauMoniteur);
    }

    public function setPhotoDeProfil($photoDeProfil): void
    {
        $this->photoDeProfil = $photoDeProfil;
    }

    public function setCarteNiveau($carteNiveau): void
    {
        $this->carteNiveau = $carteNiveau;
    }

    public function setCertificatMedical($certificatMedical): void
    {
        $this->certificatMedical = $certificatMedical;
    }
}
