<?php

namespace App\Entity;

use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Repository\PlongeurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PlongeurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['plongeur' => Plongeur::class, 'moniteur' => Moniteur::class])]

class Plongeur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?bool $formation = null;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeAutonome::class)]
    private ?NiveauPlongeeAutonome $niveauPlongeeAutonome = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'plongeur')]
    private Collection $commentaires;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'inscriptions')]
    private Collection $sorties;

    /**
     * @var Collection<int, Palanquee>
     */
    #[ORM\ManyToMany(targetEntity: Palanquee::class, mappedBy: 'assigner')]
    private Collection $palanqueesParticipees;

    #[ORM\Column(type: 'integer', nullable: true, enumType: NiveauPlongeeEncadre::class)]
    private ?NiveauPlongeeEncadre $niveauPlongeeEncadre = null;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->sorties = new ArrayCollection();
        $this->palanqueesParticipees = new ArrayCollection();
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

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function setNiveauPlongeeAutonome(NiveauPlongeeAutonome $niveauPlongeeAutonome): static
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

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesDP(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->addInscription($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            $sorty->removeInscription($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Palanquee>
     */
    public function getPalanqueesParticipees(): Collection
    {
        return $this->palanqueesParticipees;
    }

    public function addPalanqueesParticipee(Palanquee $palanqueesParticipee): static
    {
        if (!$this->palanqueesParticipees->contains($palanqueesParticipee)) {
            $this->palanqueesParticipees->add($palanqueesParticipee);
            $palanqueesParticipee->addAssigner($this);
        }

        return $this;
    }

    public function removePalanqueesParticipee(Palanquee $palanqueesParticipee): static
    {
        if ($this->palanqueesParticipees->removeElement($palanqueesParticipee)) {
            $palanqueesParticipee->removeAssigner($this);
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
}
