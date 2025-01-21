<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Repository\PlongeurRepository;
use App\State\Palanquee\PalanqueePlongeursProvider;
use App\State\Plongeur\PlongeurPatchProcessor;
use App\State\Plongeur\PlongeurPostProcessor;
use App\State\Sortie\SortieNotAssignedPlongeursProvider;
use App\State\Sortie\SortiePlongeursProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: PlongeurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            normalizationContext: ["groups" => ["plongeur:read"]],
        ),
        new Post(
            denormalizationContext: ["groups" => ["plongeur:create"]],
            validationContext: ["groups" => ["Default", "plongeur:create"]],
            processor: PlongeurPostProcessor::class
        ),
        new Patch(
            denormalizationContext: ["groups" => ["plongeur:update"]],
            security: "is_granted('PLONGEUR_EDIT', object)",
            validationContext: ["groups" => ["Default", "plongeur:update"]],
            processor: PlongeurPatchProcessor::class
        ),
        new Delete(
            security: "is_granted('PLONGEUR_EDIT', object)"
        ),
        new GetCollection(
            uriTemplate: '/sorties/{idSortie}/plongeurs',
            uriVariables: [
                'idSortie' => new Link(
                    toProperty: 'sortie',
                    fromClass: Sortie::class,
                ),
            ],
            description: "Récupère les plongeurs d'une sortie.",
            normalizationContext: ['groups' => ['inscriptionSortie:plongeur:read']],
            provider: SortiePlongeursProvider::class
        ),
        new GetCollection(
            uriTemplate: '/palanquees/{idPalanquee}/plongeurs',
            uriVariables: [
                'idPalanquee' => new Link(
                    toProperty: 'palanquee',
                    fromClass: Palanquee::class,
                ),
            ],
            description: "Récupère les plongeurs d'une palanquee.",
            normalizationContext: ['groups' => ['assignationPalanquee:plongeur:read']],
            provider: PalanqueePlongeursProvider::class
        ),
        new GetCollection(
            uriTemplate: '/sorties/{idSortie}/plongeursNotAssigned',
            uriVariables: [
                'idSortie' => new Link(
                    toProperty: 'sortie',
                    fromClass: Sortie::class,
                ),
            ],
            description: "Récupère les plongeurs d'une sortie qui ne sont pas assigné a une palanqueé ou encadrant d'une palanquée.",
            normalizationContext: ['groups' => ['plongeur:read']],
            provider: SortieNotAssignedPlongeursProvider::class
        ),
    ],
    normalizationContext: ["groups" => ["plongeur:read", 'inscriptionSortie:sortie:read', 'assignationPalanquee:palanquee:read']],
)]
class Plongeur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plongeur:read', 'inscriptionSortie:plongeur:read', 'assignationPalanquee:plongeur:read', 'inscriptionSortie:sortie:read'])]
    private ?int $id = null;

    #[NotBlank(groups: ["plongeur:create"])]
    #[NotNull(groups: ["plongeur:create"])]
    #[ORM\Column(length: 180)]
    #[Groups(['plongeur:read', "plongeur:create", 'inscriptionSortie:plongeur:read', 'assignationPalanquee:plongeur:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $password = null;

    #[NotNull(groups: ["plongeur:create"])]
    #[NotBlank(groups: ["plongeur:create"])]
    #[ApiProperty(readable: false)]
    #[Groups(["plongeur:create", "plongeur:update"])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 100)]
    #[NotNull(groups: ["plongeur:create"])]
    #[NotBlank(groups: ["plongeur:create"])]
    #[Groups(["plongeur:read","plongeur:create", "plongeur:update", 'inscriptionSortie:plongeur:read', 'assignationPalanquee:plongeur:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[NotNull(groups: ["plongeur:create"])]
    #[NotBlank(groups: ["plongeur:create"])]
    #[Groups(["plongeur:read","plongeur:create", "plongeur:update", 'inscriptionSortie:plongeur:read', 'assignationPalanquee:plongeur:read'])]
    private ?string $prenom = null;

    #[ORM\Column]
    #[NotNull(groups: ["plongeur:create"])]
    #[NotBlank(groups: ["plongeur:create"])]
    #[Groups(["plongeur:read","plongeur:create", "plongeur:update"])]
    private ?bool $formation = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeAutonome::class)]
    #[Groups(["plongeur:read", "plongeur:create", "plongeur:update"])]
    private ?NiveauPlongeeAutonome $niveauPlongeeAutonome = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'plongeur')]
    private Collection $commentaires;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeEncadre::class)]
    #[Groups(["plongeur:read","plongeur:create", "plongeur:update"])]
    private ?NiveauPlongeeEncadre $niveauPlongeeEncadre = null;


    #[ORM\Column(type: 'string', length: 25, enumType: NiveauMoniteur::class)]
    #[Groups(["plongeur:read","plongeur:update"])]
    private ?NiveauMoniteur $niveauMoniteur = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'directeurPlongee')]
    private Collection $sortiesDP;

    /**
     * @var Collection<int, Palanquee>
     */
    #[ORM\OneToMany(targetEntity: Palanquee::class, mappedBy: 'encadrant')]
    private Collection $palanquees;

    #[ORM\Column]
    #[Groups(["plongeur:read","plongeur:update"])]
    #[ApiProperty(readable: true, writable: true)]
    private ?bool $isMoniteur = null;

    /**
     * @var Collection<int, InscriptionSortie>
     */
    #[ORM\OneToMany(targetEntity: InscriptionSortie::class, mappedBy: 'plongeur', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(["plongeur:read"])]
    #[ApiProperty(readable: true)]
    private Collection $sorties;

    /**
     * @var Collection<int, AssignationPalanquee>
     */
    #[ORM\OneToMany(targetEntity: AssignationPalanquee::class, mappedBy: 'plongeur', cascade: ['persist'], orphanRemoval: true)]
    private Collection $assignationPalanquees;

    /**
     * @var Collection<int, IgnoredSorties>
     */
    #[ORM\OneToMany(targetEntity: IgnoredSorties::class, mappedBy: 'plongeur')]
    #[Groups(["plongeur:read"])]
    private Collection $ignoredSorties;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["plongeur:read"])]
    private ?string $tailleStab = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["plongeur:read"])]
    private ?string $taillePalmes = null;

    #[ORM\Column]
    #[Groups(["plongeur:read"])]
    private ?bool $possedeMasquePlongee = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["plongeur:read"])]
    private ?string $tailleCombinaison = null;

    #[ORM\Column(type: Types::BLOB)]
    private $photoDeProfil = null;

    #[ORM\Column(type: Types::BLOB)]
    private $carteNiveau = null;

    #[ORM\Column(type: Types::BLOB)]
    private $certificatMedical = null;


    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->sortiesDP = new ArrayCollection();
        $this->palanquees = new ArrayCollection();
        $this->sorties = new ArrayCollection();
        $this->assignationPalanquees = new ArrayCollection();
        $this->ignoredSorties = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return list<string>
     * @see UserInterface
     *
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
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

    public function getNiveauPlongeeAutonome(): ?NiveauPlongeeAutonome
    {
        return $this->niveauPlongeeAutonome;
    }

    public function setNiveauPlongeeAutonome(?NiveauPlongeeAutonome $niveauPlongeeAutonome): static
    {
        $this->niveauPlongeeAutonome = $niveauPlongeeAutonome;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPlongeur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getPlongeur() === $this) {
                $commentaire->setPlongeur(null);
            }
        }

        return $this;
    }


    public function getNiveauPlongeeEncadre(): ?NiveauPlongeeEncadre
    {
        return $this->niveauPlongeeEncadre;
    }

    public function setNiveauPlongeeEncadre(?NiveauPlongeeEncadre $niveauPlongeeEncadre): static
    {
        $this->niveauPlongeeEncadre = $niveauPlongeeEncadre;

        return $this;
    }


    public function getNiveauMoniteur(): ?NiveauMoniteur
    {
        return $this->niveauMoniteur;
    }

    public function setNiveauMoniteur(NiveauMoniteur $niveauMoniteur): static
    {
        $this->niveauMoniteur = $niveauMoniteur;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesDP(): Collection
    {
        return $this->sortiesDP;
    }

    public function addSortieDP(Sortie $sorty): static
    {
        if (!$this->sortiesDP->contains($sorty)) {
            $this->sortiesDP->add($sorty);
            $sorty->setDirecteurPlongee($this);
        }

        return $this;
    }

    public function removeSortieDP(Sortie $sorty): static
    {
        if ($this->sortiesDP->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getDirecteurPlongee() === $this) {
                $sorty->setDirecteurPlongee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Palanquee>
     */
    public function getPalanquees(): Collection
    {
        return $this->palanquees;
    }

    public function addPalanquee(Palanquee $palanquee): static
    {
        if (!$this->palanquees->contains($palanquee)) {
            $this->palanquees->add($palanquee);
            $palanquee->setEncadrant($this);
        }

        return $this;
    }

    public function removePalanquee(Palanquee $palanquee): static
    {
        if ($this->palanquees->removeElement($palanquee)) {
            // set the owning side to null (unless already changed)
            if ($palanquee->getEncadrant() === $this) {
                $palanquee->setEncadrant(null);
            }
        }

        return $this;
    }

    public function isMoniteur(): ?bool
    {
        return $this->isMoniteur;
    }

    public function setMoniteur(bool $isMoniteur): static
    {
        $this->isMoniteur = $isMoniteur;

        return $this;
    }

    public function __toString(): string
    {
        // Exemple : Retourner le nom complet du plongeur
        return $this->getPrenom() . ' ' . $this->getNom();
    }

    public function addRole($role) : void {
        if(!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole($role) : void {
        $index = array_search($role, $this->roles);
        if ($index !== false) {
            unset($this->roles[$index]);
        }
    }

    /**
     * @return Collection<int, InscriptionSortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(InscriptionSortie $inscriptionSorty): static
    {
        if (!$this->sorties->contains($inscriptionSorty)) {
            $this->sorties->add($inscriptionSorty);
            $inscriptionSorty->setPlongeur($this);
        }

        return $this;
    }

    public function removeSorty(InscriptionSortie $inscriptionSorty): static
    {
        if ($this->sorties->removeElement($inscriptionSorty)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionSorty->getPlongeur() === $this) {
                $inscriptionSorty->setPlongeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AssignationPalanquee>
     */
    public function getAssignationPalanquees(): Collection
    {
        return $this->assignationPalanquees;
    }

    public function addAssignationPalanquee(AssignationPalanquee $assignationPalanquee): static
    {
        if (!$this->assignationPalanquees->contains($assignationPalanquee)) {
            $this->assignationPalanquees->add($assignationPalanquee);
            $assignationPalanquee->setPlongeur($this);
        }

        return $this;
    }

    public function removeAssignationPalanquee(AssignationPalanquee $assignationPalanquee): static
    {
        if ($this->assignationPalanquees->removeElement($assignationPalanquee)) {
            // set the owning side to null (unless already changed)
            if ($assignationPalanquee->getPlongeur() === $this) {
                $assignationPalanquee->setPlongeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, IgnoredSorties>
     */
    public function getIgnoredSorties(): Collection
    {
        return $this->ignoredSorties;
    }

    public function addIgnoredSorty(IgnoredSorties $ignoredSorty): static
    {
        if (!$this->ignoredSorties->contains($ignoredSorty)) {
            $this->ignoredSorties->add($ignoredSorty);
            $ignoredSorty->setPlongeur($this);
        }

        return $this;
    }

    public function removeIgnoredSorty(IgnoredSorties $ignoredSorty): static
    {
        if ($this->ignoredSorties->removeElement($ignoredSorty)) {
            // set the owning side to null (unless already changed)
            if ($ignoredSorty->getPlongeur() === $this) {
                $ignoredSorty->setPlongeur(null);
            }
        }

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

    public function getPhotoDeProfil()
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
        return $this->carteNiveau;
    }

    public function setCarteNiveau($carteNiveau): static
    {
        $this->carteNiveau = $carteNiveau;

        return $this;
    }

    public function getCertificatMedical()
    {
        return $this->certificatMedical;
    }

    public function setCertificatMedical($certificatMedical): static
    {
        $this->certificatMedical = $certificatMedical;

        return $this;
    }
}
