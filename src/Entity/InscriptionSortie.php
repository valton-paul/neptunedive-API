<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\InscriptionSortieRepository;
use App\State\Inscription\InscriptionSortieProcessor;
use App\State\Inscription\InscriptionSortieProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: InscriptionSortieRepository::class)]
#[UniqueEntity(fields: ['sortie', 'plongeur'], message: "Un plongeur ne peut pas être inscrit plus d'une fois a une même sortie.")]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_SORTIE_PLONGEUR', fields: ['sortie', 'plongeur'])]
#[ApiResource(
    operations: [
        new Post(
            description: "Crée une inscription pour un plongeur et une sortie.",
            denormalizationContext: ["groups" => ['inscriptionSortie:write']],
            validationContext: ["groups" => ['Default', 'inscriptionSortie:write']]
        ),
        new Delete(
            description: "Supprime l'inscription qui correspond au plongeur et à la sortie dans le corps de la requete.",
        )
    ],
    normalizationContext: ["groups" => ['inscriptionSortie:read']],

)]
#[ApiResource(
    uriTemplate: '/plongeurs/{idPlongeur}/sorties/{idSortie}',
    operations: [
        new Get(
            description: "Récupère l'inscription qui correspond a idPlongeur et à idSortie donnés dans l'url.",
        ),
        new Delete(
            description: "Supprime l'inscription qui correspond a idPlongeur et à idSortie donnés dans l'url.",
        ),
        new Put(
            description: "Crée ou met à jour l'inscription qui correspond a idPlongeur et à idSortie donnés dans l'urlmlml.",
            deserialize: false,
            processor: InscriptionSortieProcessor::class,
            allowCreate: true,
        ),
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
    normalizationContext: ["groups" => ['inscriptionSortie:sortie:read']],
    provider: InscriptionSortieProvider::class
)]
class InscriptionSortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['inscriptionSortie:read', 'inscriptionSortie:sortie:read', 'inscriptionSortie:plongeur:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull(groups: ['inscriptionSortie:write'])]
    #[NotBlank(groups: ['inscriptionSortie:write'])]
    #[Groups(['inscriptionSortie:read', "inscriptionSortie:write", 'inscriptionSortie:sortie:read'])]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotNull(groups: ['inscriptionSortie:write'])]
    #[NotBlank(groups: ['inscriptionSortie:write'])]
    #[Groups(['inscriptionSortie:read', "inscriptionSortie:write", 'inscriptionSortie:plongeur:read'])]
    private ?Plongeur $plongeur = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlongeur(): ?Plongeur
    {
        return $this->plongeur;
    }

    public function setPlongeur(?Plongeur $plongeur): static
    {
        $this->plongeur = $plongeur;

        return $this;
    }
}
