<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\CommentaireRepository;
use App\State\Commentaire\CommentaireProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(security: "is_granted('SUPPRIMER_COMMENTAIRE', object)"),
        new GetCollection(),
        new Post(
            denormalizationContext: ["groups" => ["Default", "commentaire:create"]],
            security: "is_granted('ECRIRE_COMMENTAIRE', object)",
            validationContext: ["groups" => ["Default", "commentaire:create"]],
            processor: CommentaireProcessor::class,
        ),
        new Patch(
            denormalizationContext: ["groups" => ["Default", "commentaire:update"]],
            security: "is_granted('MODIFIER_COMMENTAIRE', object)",
            validationContext: ["groups" => ["Default", "commentaire:update"]],
            processor: CommentaireProcessor::class
        ),
        new GetCollection(
            uriTemplate: '/plongeurs/{idPlongeur}/commentaires',
            uriVariables: [
                'idPlongeur' => new Link(
                    fromProperty: 'commentaires',
                    fromClass: Plongeur::class,
                ),
            ],
            description: "Récupère les commentaires d'un plongeur.",
            normalizationContext: ["groups" => ["commentaire:read", "plongeur:read", "sortie:read"]],
        )
    ],
    normalizationContext: ["groups" => ["commentaire:read"]],
    //security: "is_granted('MONITEUR_ACCES', object)"
    order: ["dateCommentaire" => "DESC"]
)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["commentaire:read"])]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[Groups(["commentaire:create", "commentaire:update", "commentaire:read"])]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(["commentaire:read"])]
    private ?\DateTimeInterface $dateCommentaire = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["commentaire:read", "commentaire:update", "commentaire:create"])]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["commentaire:read", "commentaire:create"])]
    private ?Plongeur $plongeur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["commentaire:read"])]
    private ?Plongeur $moniteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDateCommentaire(): ?\DateTimeInterface
    {
        return $this->dateCommentaire;
    }

    public function setDateCommentaire(\DateTimeInterface $dateCommentaire): static
    {
        $this->dateCommentaire = $dateCommentaire;

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

    public function getPlongeur(): ?Plongeur
    {
        return $this->plongeur;
    }

    public function setPlongeur(?Plongeur $plongeur): static
    {
        $this->plongeur = $plongeur;

        return $this;
    }

    public function getMoniteur(): ?Plongeur
    {
        return $this->moniteur;
    }

    public function setMoniteur(?Plongeur $moniteur): static
    {
        $this->moniteur = $moniteur;

        return $this;
    }
}
