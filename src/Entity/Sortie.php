<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\Creneaux;
use App\Repository\SortieRepository;
use App\State\Plongeur\PlongeurSortiesHistoriqueProvider;
use App\State\Plongeur\PlongeurSortiesNotEnrolledProvider;
use App\State\Plongeur\PlongeurSortiesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(order: ["dateSortie" => "DESC"] ),
        new Post(
            denormalizationContext: ["groups" => ["sortie:create"]],
            security: "is_granted('ROLE_MONITEUR')",
            validationContext: ["groups" => ["Default", "sortie:create"]],
        ),
        new Patch(
            denormalizationContext: ["groups" => ["sortie:update"]],
            security: "is_granted('ROLE_MONITEUR')",
            validationContext: ["groups" => ["Default", "sortie:update"]]
        ),
        new Delete(
            security: "is_granted('ROLE_MONITEUR')",
        ),
        new GetCollection(
            uriTemplate: '/plongeurs/{idPlongeur}/sorties',
            uriVariables: [
                'idPlongeur' => new Link(
                    toProperty: 'plongeur',
                    fromClass: Plongeur::class,
                ),
            ],
            description: "Récupère les sorties d'un plongeur.",
            normalizationContext: ['groups' => ['inscriptionSortie:sortie:read', "sortie:read"]],
            provider: PlongeurSortiesProvider::class
        ),
        new GetCollection(
            uriTemplate: '/plongeurs/{idPlongeur}/sorties-not-inscrit',
            uriVariables: [
                'idPlongeur' => new Link(
                    toProperty: 'plongeur',
                    fromClass: Plongeur::class,
                ),
            ],
            description: "Récupère les sorties où le plongeur n'est pas inscrit.",
            normalizationContext: ['groups' => ['inscriptionSortie:sortie:read', "sortie:read"]],
            provider: PlongeurSortiesNotEnrolledProvider::class
        ),
        new GetCollection(
            uriTemplate: '/plongeurs/{idPlongeur}/historique',
            uriVariables: [
                'idPlongeur' => new Link(
                    toProperty: 'plongeur',
                    fromClass: Plongeur::class,
                ),
            ],
            description: "Récupère l'historique des sorties d'un plongeur.",
            normalizationContext: ['groups' => ['inscriptionSortie:sortie:read', "sortie:read"]],
            provider: PlongeurSortiesHistoriqueProvider::class
        )
    ],
    normalizationContext: ["groups" => ["sortie:read",'inscriptionSortie:plongeur:read', 'lieu:read']]
)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sortie:read', 'inscriptionSortie:sortie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[NotBlank(groups: ['sortie:create'])]
    #[NotNull(groups: ['sortie:create'])]
    #[Groups(['sortie:read', 'sortie:create', "sortie:update", 'inscriptionSortie:sortie:read'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[NotBlank(groups: ['sortie:create'])]
    #[NotNull(groups: ['sortie:create'])]
    #[Groups(['sortie:read', 'inscriptionSortie:sortie:read', 'sortie:create', "sortie:update"])]
    private ?\DateTimeInterface $dateSortie = null;

    #[ORM\ManyToOne(targetEntity: Plongeur::class,inversedBy: 'sortiesDP')]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(groups: ['sortie:create'])]
    #[NotNull(groups: ['sortie:create'])]
    #[Groups(['sortie:read', 'sortie:create', "sortie:update"])]
    private ?Plongeur $directeurPlongee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(groups: ['sortie:create'])]
    #[NotNull(groups: ['sortie:create'])]
    #[Groups(['sortie:read', 'sortie:create', "sortie:update"])]
    private ?Plongeur $securiteSurface = null;

    /**
     * @var Collection<int, Palanquee>
     */
    #[ORM\OneToMany(targetEntity: Palanquee::class, mappedBy: 'sortie')]
    #[Groups(['sortie:read'])]
    private Collection $palanquees;


    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[Groups(['sortie:read', "sortie:update"])]
    private ?Bateau $bateau = null;

    #[ORM\ManyToOne]
    #[Groups(['sortie:read', "sortie:update"])]
    private ?Plongeur $piloteBateau = null;

    /**
     * @var Collection<int, InscriptionSortie>
     */
    #[ORM\OneToMany(targetEntity: InscriptionSortie::class, mappedBy: 'sortie', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['sortie:read'])]
    private Collection $inscriptions;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(groups: ['sortie:create'])]
    #[NotNull(groups: ['sortie:create'])]
    #[Groups(['sortie:read', 'inscriptionSortie:sortie:read', 'sortie:create', "sortie:update"])]
    private ?Lieu $idLieu = null;

    #[ORM\Column(length: 255)]
    #[Groups(['sortie:read', 'inscriptionSortie:sortie:read', 'sortie:create', "sortie:update"])]
    private ?string $heureSortie = null;

    #[ORM\Column]
    #[Groups(['sortie:read', 'inscriptionSortie:sortie:read', 'sortie:create', "sortie:update"])]
    private ?int $nbMaxInscription = null;

    public function __construct()
    {
        $this->palanquees = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(\DateTimeInterface $dateSortie): static
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }


    public function getDirecteurPlongee(): ?Plongeur
    {
        return $this->directeurPlongee;
    }

    public function setDirecteurPlongee(?Plongeur $directeurPlongee): static
    {
        $this->directeurPlongee = $directeurPlongee;

        return $this;
    }

    public function getSecuriteSurface(): ?Plongeur
    {
        return $this->securiteSurface;
    }

    public function setSecuriteSurface(?Plongeur $securiteSurface): static
    {
        $this->securiteSurface = $securiteSurface;

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
            $palanquee->setSortie($this);
        }

        return $this;
    }

    public function removePalanquee(Palanquee $palanquee): static
    {
        if ($this->palanquees->removeElement($palanquee)) {
            // set the owning side to null (unless already changed)
            if ($palanquee->getSortie() === $this) {
                $palanquee->setSortie(null);
            }
        }

        return $this;
    }
    

    public function getBateau(): ?Bateau
    {
        return $this->bateau;
    }

    public function setBateau(?Bateau $bateau): static
    {
        $this->bateau = $bateau;

        return $this;
    }

    public function getPiloteBateau(): ?Plongeur
    {
        return $this->piloteBateau;
    }

    public function setPiloteBateau(?Plongeur $piloteBateau): static
    {
        $this->piloteBateau = $piloteBateau;

        return $this;
    }

    /**
     * @return Collection<int, InscriptionSortie>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscriptionSorty(InscriptionSortie $inscriptionSorty): static
    {
        if (!$this->inscriptions->contains($inscriptionSorty)) {
            $this->inscriptions->add($inscriptionSorty);
            $inscriptionSorty->setSortie($this);
        }

        return $this;
    }

    public function removeInscriptionSorty(InscriptionSortie $inscriptionSorty): static
    {
        if ($this->inscriptions->removeElement($inscriptionSorty)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionSorty->getSortie() === $this) {
                $inscriptionSorty->setSortie(null);
            }
        }

        return $this;
    }

    public function getIdLieu(): ?Lieu
    {
        return $this->idLieu;
    }

    public function setIdLieu(?Lieu $idLieu): static
    {
        $this->idLieu = $idLieu;

        return $this;
    }

    public function getHeureSortie(): ?string
    {
        return $this->heureSortie;
    }

    public function setHeureSortie(string $heureSortie): static
    {
        $this->heureSortie = $heureSortie;

        return $this;
    }

    public function getNbMaxInscription(): ?int
    {
        return $this->nbMaxInscription;
    }

    public function setNbMaxInscription(?int $nbMaxInscription): void
    {
        $this->nbMaxInscription = $nbMaxInscription;
    }
}
