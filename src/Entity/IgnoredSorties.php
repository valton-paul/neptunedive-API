<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\CreateProvider;
use App\Repository\IgnoredSortiesRepository;
use App\State\IgnoredSortieProcessor;
use App\State\IgnoredSortieProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IgnoredSortiesRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_IGNORED_PLONGEUR_SORTIE', fields: ['plongeur', 'sortie'])]
#[UniqueEntity(fields: ['plongeur', 'sortie'], message: "Un plongeur ne peut pas ignorer plus d'une fois la même sortie.")]
#[ApiResource(
    operations: [
        new Post(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['ignored_sorties:read']],
    denormalizationContext: ['groups' => ['ignored_sorties:write']],
    validationContext: ['groups' => ['Default', 'ignored_sorties:write']]

)]
#[ApiResource(
    uriTemplate: '/plongeurs/{idPlongeur}/ignored_sorties/{idSortie}',
    operations: [
        new Delete(),
        new Put(deserialize: false, processor: IgnoredSortieProcessor::class, allowCreate: true),
    ],
    uriVariables: [
        'idPlongeur' => new Link(
            toProperty: 'plongeur',
            fromClass: Plongeur::class,
        ),
        'idSortie' => new Link(
            toProperty: 'sortie',
            fromClass: Sortie::class,
        )
    ],
    normalizationContext: ["groups" => ['ignored_sorties:read']],
    provider: IgnoredSortieProvider::class
)]
#[ApiResource(
    uriTemplate: '/plongeurs/{idPlongeur}/ignored_sorties',
    operations: [
        new Post(provider: CreateProvider::class),
        new GetCollection(),
    ],
    uriVariables: [
        'idPlongeur' => new Link(toProperty: 'plongeur', fromClass: Plongeur::class)
    ],
    normalizationContext: ['groups' => ['sortie:read']],
    denormalizationContext: ['groups' => ['ignored_sorties:write']],
    validationContext: ['groups' => ['Default', 'ignored_sorties:write']]
)]
class IgnoredSorties
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ignoredSorties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(groups: ['ignored_sorties:write'])]
    #[Assert\NotNull(groups: ['ignored_sorties:write'])]
    #[Groups(['ignored_sorties:read', 'ignored_sorties:write'])]
    private ?Plongeur $plongeur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(groups: ['ignored_sorties:write'])]
    #[Assert\NotNull(groups: ['ignored_sorties:write'])]
    #[Groups(['ignored_sorties:write', 'plongeur:read', 'sortie:read'])]
    private ?Sortie $sortie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlongeur(): ?Plongeur
    {
        return $this->plongeur;
    }

    public function setPlongeur(?Plongeur $plongeur): static
    {
        $this->plongeur = $plongeur;

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
}
