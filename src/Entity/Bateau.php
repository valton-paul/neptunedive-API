<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\BateauRepository;
use App\Security\Voter\MoniteurVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: BateauRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(),
        new GetCollection(),
        new Post(denormalizationContext: ["groups" => ["Default", "bateau:create"]],
            validationContext: ["groups" => ["Default", "bateau:create"]]),
        new Patch(denormalizationContext: ["groups" => ["Default", "bateau:update"]],
            validationContext: ["groups" => ["Default", "bateau:update"]])
    ],
    normalizationContext: ["groups" => ["bateau:read"]],
)]
class Bateau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["bateau:read"])]
    private ?int $id = null;

    #[Groups(["bateau:create", "bateau:read", "bateau:update", "sortie:read"])]
    #[ORM\Column(length: 50)]
    #[NotBlank(groups: ["Default", "bateau:create"])]
    #[NotNull(groups: ["Default", "bateau:create"])]
    private ?string $immatriculation = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'bateau')]
    private Collection $sorties;

    #[ORM\Column]
    #[Groups(["bateau:create", "bateau:update", "bateau:read"])]
    #[NotBlank(groups: ["Default", "bateau:create"])]
    #[NotNull(groups: ["Default", "bateau:create"])]
    private ?int $nbPlaces = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImmatriculation(): ?string
    {
        return $this->immatriculation;
    }

    public function setImmatriculation(string $immatriculation): static
    {
        $this->immatriculation = $immatriculation;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setBateau($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getBateau() === $this) {
                $sorty->setBateau(null);
            }
        }

        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }

    public function setNbPlaces(int $nbPlaces): static
    {
        $this->nbPlaces = $nbPlaces;

        return $this;
    }
}
