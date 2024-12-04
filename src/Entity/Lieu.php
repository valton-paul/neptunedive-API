<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            denormalizationContext: ['groups' => ['lieu:create', 'lieu:update']]
        ),
        new GetCollection(),
        new Post(inputFormats: ['multipart' => ['multipart/form-data']]
        ),
        new Delete()
    ],
    normalizationContext: ['groups' => ['lieu:read']],
)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["lieu:read", "sortie:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["lieu:read", "sortie:read", "lieu:create", "lieu:update"])]
    #[Assert\NotBlank(groups: ["Default", "lieu:create"])]
    #[Assert\NotNull(groups: ["Default", "lieu:create"])]
    private ?string $nomLieu = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(["lieu:read", "sortie:read", "lieu:create", "lieu:update"])]
    private ?string $description = null;

    #[ORM\Column(type: "blob", nullable: true)]
    #[Groups(["lieu:read", "sortie:read", "lieu:create", "lieu:update"])]
    private $photo;

    #[ORM\Column(length: 512)]
    #[Groups(["lieu:read", "sortie:read", "lieu:create", "lieu:update"])]
    #[Assert\NotBlank(groups: ["Default", "lieu:create"])]
    #[Assert\NotNull(groups: ["Default", "lieu:create"])]
    private ?string $lienMaps = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'idLieu')]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNomLieu(): ?string
    {
        return $this->nomLieu;
    }

    public function setNomLieu(?string $nomLieu): void
    {
        $this->nomLieu = $nomLieu;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPhoto(): ?string
    {
        if (is_resource($this->photo)) {
            return base64_encode(stream_get_contents($this->photo));
        }

        return null;
    }

    public function getPhotoResource(): mixed
    {
        return $this->photo;
    }

    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    public function getLienMaps(): ?string
    {
        return $this->lienMaps;
    }

    public function setLienMaps(?string $lienMaps): void
    {
        $this->lienMaps = $lienMaps;
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
            $sorty->setIdLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getIdLieu() === $this) {
                $sorty->setIdLieu(null);
            }
        }

        return $this;
    }

}
