<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(inputFormats: ['multipart' => ['multipart/form-data']])
    ],
    normalizationContext: ['groups' => ['lieu:read']],
    denormalizationContext: ['groups' => ['lieu:create', 'lieu:update']]
)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("lieu:read")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["lieu:read", "lieu:create", "lieu:update"])]
    #[Assert\NotBlank(groups: ["Default", "lieu:create"])]
    #[Assert\NotNull(groups: ["Default", "lieu:create"])]
    private ?string $nomLieu = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Groups(["lieu:read", "lieu:create", "lieu:update"])]
    private ?string $description = null;

    #[ORM\Column(type: "blob", nullable: true)]
    #[Groups(["lieu:read", "lieu:create", "lieu:update"])]
    private $photo;

    #[ORM\Column(length: 512)]
    #[Groups(["lieu:read", "lieu:create", "lieu:update"])]
    #[Assert\NotBlank(groups: ["Default", "lieu:create"])]
    #[Assert\NotNull(groups: ["Default", "lieu:create"])]
    private ?string $lienMaps = null;

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
            return stream_get_contents($this->photo);
        }

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

}
