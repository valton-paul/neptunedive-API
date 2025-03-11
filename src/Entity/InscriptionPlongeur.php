<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DTO\CreateInscriptionInput;
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
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InscriptionPlongeurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            input: CreateInscriptionInput::class,
            name: 'create_inscription',
            processor: InscriptionPlongeurProcessor::class,
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
        new Post(
            uriTemplate: '/inscriptions/valider_email/{token}',
            uriVariables: [
                'token' => new Link(
                    fromClass: InscriptionPlongeur::class,
                    identifiers: ['verificationToken'],
                    compositeIdentifier: false
                ),
            ],
            input: false,
            name: 'finalize_inscription',
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
    normalizationContext: ['groups' => ['inscription:read']],
)]
class InscriptionPlongeur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['inscription:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    #[Groups(['inscription:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Groups(['inscription:read'])]
    private ?string $prenom = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['inscription:read'])]
    private ?bool $formation = false;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeAutonome::class)]
    #[Groups(['inscription:read'])]
    private ?NiveauPlongeeAutonome $niveauPlongeeAutonome = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeEncadre::class)]
    #[Groups(['inscription:read'])]
    private ?NiveauPlongeeEncadre $niveauPlongeeEncadre = null;

    #[ORM\Column(type: 'string', nullable: true, length: 25, enumType: NiveauMoniteur::class)]
    #[Groups(['inscription:read'])]
    private ?NiveauMoniteur $niveauMoniteur = null;

    #[ORM\Column]
    #[Groups(['inscription:read'])]
    private ?bool $validee = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $photoDeProfil = null;

    #[ORM\Column(type: Types::BLOB)]
    private $carteNiveau = null;

    #[ORM\Column(type: Types::BLOB)]
    private $certificatMedical = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $tokenExpiration = null;

    #[ORM\Column(length: 255)]
    #[Groups(['inscription:read'])]
    private ?string $tailleStab = null;

    #[ORM\Column(length: 255)]
    #[Groups(['inscription:read'])]
    private ?string $taillePalmes = null;

    #[ORM\Column]
    #[Groups(['inscription:read'])]
    private ?bool $possedeMasquePlongee = null;

    #[ORM\Column(length: 255)]
    #[Groups(['inscription:read'])]
    private ?string $tailleCombinaison = null;

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

    public function getPhotoDeProfil()
    {
        if (is_resource($this->photoDeProfil)) {
            return base64_encode(stream_get_contents($this->photoDeProfil));
        }

        return null;
    }

    public function getPhotoDeProfilRessource()
    {
        return $this->photoDeProfil;
    }

    public function setPhotoDeProfil($photoDeProfil): static
    {
        $this->photoDeProfil = $photoDeProfil;

        return $this;
    }

    public function getCarteNiveau()
    {
        if (is_resource($this->carteNiveau)) {
            return base64_encode(stream_get_contents($this->carteNiveau));
        }

        return null;
    }

    public function getCarteNiveauRessource()
    {
        return $this->carteNiveau;
    }


    public function setCarteNiveau($carteNiveau): static
    {
        $this->carteNiveau = $carteNiveau;

        return $this;
    }

    public function getCertificatMedical()
    {
        if (is_resource($this->certificatMedical)) {
            return base64_encode(stream_get_contents($this->certificatMedical));
        }

        return null;
    }

    public function getCertificatMedicalRessource()
    {
        return $this->certificatMedical;
    }


    public function setCertificatMedical($certificatMedical): static
    {
        $this->certificatMedical = $certificatMedical;

        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function getTokenExpiration(): ?\DateTimeInterface
    {
        return $this->tokenExpiration;
    }

    public function setTokenExpiration(?\DateTimeInterface $tokenExpiration): static
    {
        $this->tokenExpiration = $tokenExpiration;

        return $this;
    }

    public function getTailleStab(): ?string
    {
        return $this->tailleStab;
    }

    public function setTailleStab(?string $tailleStab): static
    {
        $this->tailleStab = $tailleStab;

        return $this;
    }

    public function getTaillePalmes(): ?string
    {
        return $this->taillePalmes;
    }

    public function setTaillePalmes(?string $taillePalmes): static
    {
        $this->taillePalmes = $taillePalmes;

        return $this;
    }

    public function isPossedeMasquePlongee(): ?bool
    {
        return $this->possedeMasquePlongee;
    }

    public function setPossedeMasquePlongee(bool $possedeMasquePlongee): static
    {
        $this->possedeMasquePlongee = $possedeMasquePlongee;

        return $this;
    }

    public function getTailleCombinaison(): ?string
    {
        return $this->tailleCombinaison;
    }

    public function setTailleCombinaison(?string $tailleCombinaison): static
    {
        $this->tailleCombinaison = $tailleCombinaison;

        return $this;
    }
}
