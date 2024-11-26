<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DTO\FinalizeInscriptionInput;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Repository\InscriptionPlongeurRepository;
use App\State\InscriptionPlongeur\InscriptionPlongeurProcessor;
use App\State\Palanquee\PalanqueePlongeursProvider;
use App\State\Plongeur\PlongeurPatchProcessor;
use App\State\Plongeur\PlongeurPostProcessor;
use App\State\Sortie\SortiePlongeursProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionPlongeurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Post(
            name: 'create_inscription',
            processor: InscriptionPlongeurProcessor::class
        ),
        new Post(
            uriTemplate: '/inscriptions/{idInscription}/finaliser',
            uriVariables: [
                'idInscription' => new Link(
                    fromClass: InscriptionPlongeur::class,
                    identifiers: ['id'],
                    compositeIdentifier: false
                ),
            ],
            input: FinalizeInscriptionInput::class,
            output: Plongeur::class,
            name: 'finalize_inscription',
            processor: InscriptionPlongeurProcessor::class
        ),
        new Post(
            uriTemplate: '/inscriptions/{idInscription}/valider',
            uriVariables: [
                'idInscription' => new Link(
                    fromClass: InscriptionPlongeur::class,
                    identifiers: ['id'],
                    compositeIdentifier: false
                ),
            ],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MONITEUR')",
            input: false,
            name: 'validate_inscription',
            processor: InscriptionPlongeurProcessor::class
        ),
        new GetCollection(
            security: ("is_granted('ROLE_ADMIN') or is_granted('ROLE_MONITEUR')")
        ),
        new Get(
            uriTemplate: '/inscriptions/{idInscription}',
            uriVariables: [
                'idInscription' => new Link(
                    fromClass: InscriptionPlongeur::class,
                    identifiers: ['id'],
                    compositeIdentifier: false
                ),
            ],
        )
    ],
//    normalizationContext: ["groups" => [""]],
)]
class InscriptionPlongeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?bool $formation = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeAutonome::class)]
    private ?NiveauPlongeeAutonome $niveauPlongeeAutonome = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeEncadre::class)]
    private ?NiveauPlongeeEncadre $niveauPlongeeEncadre = null;

    #[ORM\Column(type: 'string', nullable: true, length: 25, enumType: NiveauMoniteur::class)]
    private ?NiveauMoniteur $niveauMoniteur = null;

    #[ORM\Column]
    private ?bool $validee = null;

    /**
     * @return bool|null
     */
    public function getValidee(): ?bool
    {
        return $this->validee;
    }

    /**
     * @param bool|null $validee
     */
    public function setValidee(?bool $validee): void
    {
        $this->validee = $validee;
    }

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

    /**
     * @return NiveauMoniteur|null
     */
    public function getNiveauMoniteur(): ?NiveauMoniteur
    {
        return $this->niveauMoniteur;
    }

    /**
     * @return NiveauPlongeeAutonome|null
     */
    public function getNiveauPlongeeAutonome(): ?NiveauPlongeeAutonome
    {
        return $this->niveauPlongeeAutonome;
    }

    /**
     * @return NiveauPlongeeEncadre|null
     */
    public function getNiveauPlongeeEncadre(): ?NiveauPlongeeEncadre
    {
        return $this->niveauPlongeeEncadre;
    }

    /**
     * @param NiveauMoniteur|null $niveauMoniteur
     */
    public function setNiveauMoniteur(?NiveauMoniteur $niveauMoniteur): void
    {
        $this->niveauMoniteur = $niveauMoniteur;
    }

    /**
     * @param NiveauPlongeeAutonome|null $niveauPlongeeAutonome
     */
    public function setNiveauPlongeeAutonome(?NiveauPlongeeAutonome $niveauPlongeeAutonome): void
    {
        $this->niveauPlongeeAutonome = $niveauPlongeeAutonome;
    }

    /**
     * @param NiveauPlongeeEncadre|null $niveauPlongeeEncadre
     */
    public function setNiveauPlongeeEncadre(?NiveauPlongeeEncadre $niveauPlongeeEncadre): void
    {
        $this->niveauPlongeeEncadre = $niveauPlongeeEncadre;
    }
}
