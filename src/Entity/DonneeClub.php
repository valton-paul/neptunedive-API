<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\DonneeClubRepository;
use App\State\DonneeClub\DonneeClubProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DonneeClubRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            processor: DonneeClubProcessor::class,
        ),
        new Delete(),
        new GetCollection(),
        new Patch(
            processor: DonneeClubProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['donnee_club:read']],
)]
class DonneeClub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['donnee_club:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['donnee_club:read'])]
    private ?string $emailSpond = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $passwordSpond = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['donnee_club:read'])]
    private ?string $groupNameSpond = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['donnee_club:read'])]
    private ?string $groupIdSpond = null;

    #[ApiProperty(readable: false)]
    private ?string $plainPasswordSpond = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['donnee_club:read'])]
    private ?string $helloAssoClientId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $helloAssoClientSecret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function eraseCredentials(): void
    {
        $this->plainPasswordSpond = null;
    }

    public function getEmailSpond(): ?string
    {
        return $this->emailSpond;
    }

    public function setEmailSpond(?string $emailSpond): static
    {
        $this->emailSpond = $emailSpond;

        return $this;
    }

    public function getPasswordSpond(): ?string
    {
        return $this->passwordSpond;
    }

    public function setPasswordSpond(?string $passwordSpond): static
    {
        $this->passwordSpond = $passwordSpond;

        return $this;
    }

    public function getPlainPasswordSpond(): ?string
    {
        return $this->plainPasswordSpond;
    }

    public function setPlainPasswordSpond(?string $plainPasswordSpond): static
    {
        $this->plainPasswordSpond = $plainPasswordSpond;

        return $this;
    }

    public function getGroupNameSpond(): ?string
    {
        return $this->groupNameSpond;
    }

    public function setGroupNameSpond(?string $groupNameSpond): static
    {
        $this->groupNameSpond = $groupNameSpond;

        return $this;
    }

    public function getGroupIdSpond(): ?string
    {
        return $this->groupIdSpond;
    }

    public function setGroupIdSpond(?string $groupIdSpond): static
    {
        $this->groupIdSpond = $groupIdSpond;

        return $this;
    }

    public function getHelloAssoClientId(): ?string
    {
        return $this->helloAssoClientId;
    }

    public function setHelloAssoClientId(?string $helloAssoClientId): static
    {
        $this->helloAssoClientId = $helloAssoClientId;

        return $this;
    }

    public function getHelloAssoClientSecret(): ?string
    {
        return $this->helloAssoClientSecret;
    }

    public function setHelloAssoClientSecret(?string $helloAssoClientSecret): static
    {
        $this->helloAssoClientSecret = $helloAssoClientSecret;

        return $this;
    }
}
