<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PalanqueeRepository;
use App\State\Plongeur\PlongeurPalanqueesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: PalanqueeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ["groups" => ["palanquee:create"]],
            validationContext: ["groups" => ["Default", "palanquee:create"]]
        ),
        new Patch(
            denormalizationContext: ["groups" => ["palanquee:update", "plongeur:update"]],
            validationContext: ["groups" => ["Default", "palanquee:update"]]
        ),
        new Delete(

        ),
        new GetCollection(
            uriTemplate: '/plongeurs/{idPlongeur}/palanquees',
            uriVariables: [
                'idPlongeur' => new Link(
                    fromProperty: 'plongeur',
                    fromClass: Plongeur::class
                )
            ],
            normalizationContext: ["groups" => ["assignationPalanquee:palanquee:read"]],
            provider: PlongeurPalanqueesProvider::class
        ),
        new GetCollection(
            uriTemplate: '/sorties/{idSortie}/palanquees',
            uriVariables: [
                'idSortie' => new Link(
                    fromProperty: 'palanquees',
                    fromClass: Sortie::class
                )
            ],
            description: "Récupère les palanquées d'une sortie.",
            normalizationContext: ["groups" => ["assignationPalanquee:palanquee:read", "plongeur:read", "inscriptionSortie:sortie:read"]],
        )
    ],
    normalizationContext: ["groups" => ["palanquee:read", 'assignationPalanquee:plongeur:read']],
    security: "is_granted('ROLE_MONITEUR')"
)]
class Palanquee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['palanquee:read', 'assignationPalanquee:palanquee:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['palanquee:read', 'palanquee:create', 'palanquee:update', 'assignationPalanquee:palanquee:read'])]
    private ?\DateTimeInterface $dureePrevue = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['palanquee:read', 'palanquee:create', 'palanquee:update', 'assignationPalanquee:palanquee:read'])]
    private ?\DateTimeInterface $dureeRealisee = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['palanquee:read', 'palanquee:create', 'palanquee:update', 'assignationPalanquee:palanquee:read'])]
    private ?int $profondeurPrevue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['palanquee:read', 'palanquee:create', 'palanquee:update', 'assignationPalanquee:palanquee:read'])]
    private ?int $profondeurRealisee = null;

    #[ORM\ManyToOne(inversedBy: 'palanquees')]
    #[ORM\JoinColumn(nullable: false)]
    #[NotNull(groups: ["palanquee:create"])]
    #[NotBlank(groups: ["palanquee:create"])]
    #[Groups(['palanquee:read', 'palanquee:create', 'assignationPalanquee:palanquee:read'])]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'palanquees')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['palanquee:read', 'palanquee:create', 'palanquee:update', 'assignationPalanquee:palanquee:read'])]
    private ?Plongeur $encadrant = null;


    /**
     * @var Collection<int, AssignationPalanquee>
     */
    #[ORM\OneToMany(targetEntity: AssignationPalanquee::class, mappedBy: 'palanquee', cascade: ['persist'], orphanRemoval: true)]
    private Collection $plongeurAssignes;

    public function __construct()
    {
        $this->plongeurAssignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDureePrevue(): ?\DateTimeInterface
    {
        return $this->dureePrevue;
    }

    public function setDureePrevue(?\DateTimeInterface $dureePrevue): static
    {
        $this->dureePrevue = $dureePrevue;

        return $this;
    }

    public function getDureeRealisee(): ?\DateTimeInterface
    {
        return $this->dureeRealisee;
    }

    public function setDureeRealisee(?\DateTimeInterface $dureeRealisee): static
    {
        $this->dureeRealisee = $dureeRealisee;

        return $this;
    }

    public function getProfondeurPrevue(): ?int
    {
        return $this->profondeurPrevue;
    }

    public function setProfondeurPrevue(?int $profondeurPrevue): static
    {
        $this->profondeurPrevue = $profondeurPrevue;

        return $this;
    }

    public function getProfondeurRealisee(): ?int
    {
        return $this->profondeurRealisee;
    }

    public function setProfondeurRealisee(?int $profondeurRealisee): static
    {
        $this->profondeurRealisee = $profondeurRealisee;

        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }

    public function getEncadrant(): ?Plongeur
    {
        return $this->encadrant;
    }

    public function setEncadrant(?Plongeur $encadrant): static
    {
        $this->encadrant = $encadrant;

        return $this;
    }

    /**
     * @return Collection<int, AssignationPalanquee>
     */
    public function getPlongeurAssignes(): Collection
    {
        return $this->plongeurAssignes;
    }

    public function addPlongeurAssigne(AssignationPalanquee $plongeurAssigne): static
    {
        if (!$this->plongeurAssignes->contains($plongeurAssigne)) {
            $this->plongeurAssignes->add($plongeurAssigne);
            $plongeurAssigne->setPalanquee($this);
        }

        return $this;
    }

    public function removePlongeurAssigne(AssignationPalanquee $plongeurAssigne): static
    {
        if ($this->plongeurAssignes->removeElement($plongeurAssigne)) {
            // set the owning side to null (unless already changed)
            if ($plongeurAssigne->getPalanquee() === $this) {
                $plongeurAssigne->setPalanquee(null);
            }
        }

        return $this;
    }
}
