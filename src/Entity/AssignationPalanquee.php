<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AssignationPalanqueeRepository;
use App\State\Assignation\AssignationPalanqueeProcessor;
use App\State\Assignation\AssignationPalanqueeProvider;
use App\State\Assignation\AssignationPalanqueeSortieProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[ORM\Entity(repositoryClass: AssignationPalanqueeRepository::class)]
#[UniqueEntity(fields: ['palanquee', 'plongeur'], message: "Un plongeur ne peut pas être inscrit plus d'une fois à une même palanquée.")]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PALANQUEE_PLONGEUR', fields: ['palanquee', 'plongeur'])]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ["groups" => ['assignationPalanquee:write']],
            validationContext: ["groups" => ['Default', 'assignationPalanquee:write']]
        ),
        new Delete()
    ],
    normalizationContext: ["groups" => ['assignationPalanquee:read']],
)]
#[ApiResource(
    uriTemplate: '/plongeurs/{idPlongeur}/palanquees/{idPalanquee}',
    operations: [
        new Get(
            description: "Récupère l'assignation qui correspond à idPlongeur et à idPalanquee donnés dans l'url.",
        ),
        new Delete(
            description: "Supprime l'assignation qui correspond à idPlongeur et à idPalanquee donnés dans l'url.",
        ),
        new Put(
            description: "Crée ou met à jour l'assignation qui correspond à idPlongeur et à idPalanquee donnés dans l'url.",
            deserialize: false,
            processor: AssignationPalanqueeProcessor::class,
            allowCreate: true,
        ),
    ],
    uriVariables: [
        'idPlongeur' => new Link(
            toProperty: 'plongeur',
            fromClass: Plongeur::class,
        ),
        'idPalanquee' => new Link(
            toProperty: 'palanquee',
            fromClass: Palanquee::class,
        )
    ],
    normalizationContext: ["groups" => ['assignationPalanquee:palanquee:read', 'assignationPalanquee:plongeur:read']],
    provider: AssignationPalanqueeProvider::class
)]
#[ApiResource(
    uriTemplate: '/sorties/{idSortie}/assignationPalanquees',
    operations: [
        new GetCollection(
            description: "Récupère les assignations de palanquées pour une sortie donnée.",
        ),
    ],
    uriVariables: [
        'idSortie' => new Link(
            fromProperty: 'sortie',
            fromClass: Sortie::class,
        )
    ],
    normalizationContext: ["groups" => ['assignationPalanquee:read', 'assignationPalanquee:palanquee:read', 'plongeur:read']],
    provider: AssignationPalanqueeSortieProvider::class
)]
class AssignationPalanquee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['assignationPalanquee:read', 'assignationPalanquee:palanquee:read', 'assignationPalanquee:plongeur:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignationPalanquees')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotBlank(groups: ['assignationPalanquee:write'])]
    #[NotNull(groups: ['assignationPalanquee:write'])]
    #[Groups(['assignationPalanquee:read', 'assignationPalanquee:write', 'assignationPalanquee:plongeur:read', "sortie:read"])]
    private ?Plongeur $plongeur = null;

    #[ORM\ManyToOne(inversedBy: 'plongeurAssignes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[NotBlank(groups: ['assignationPalanquee:write'])]
    #[NotNull(groups: ['assignationPalanquee:write'])]
    #[Groups(['assignationPalanquee:read', 'assignationPalanquee:write', 'assignationPalanquee:palanquee:read'])]
    private ?Palanquee $palanquee = null;

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

    public function getPalanquee(): ?Palanquee
    {
        return $this->palanquee;
    }

    public function setPalanquee(?Palanquee $palanquee): static
    {
        $this->palanquee = $palanquee;

        return $this;
    }
}
