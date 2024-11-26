<?php

namespace App\Entity;

use App\Repository\DemandeInscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeInscriptionRepository::class)]
class DemandeInscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?bool $formation = null;

    #[ORM\Column]
    private ?bool $validee = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function isFormation(): ?bool
    {
        return $this->formation;
    }

    public function setFormation(bool $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function isValidee(): ?bool
    {
        return $this->validee;
    }

    public function setValidee(bool $validee): static
    {
        $this->validee = $validee;

        return $this;
    }
}
