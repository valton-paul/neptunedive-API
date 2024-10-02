<?php

namespace App\Entity;

use App\Repository\PalanqueeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PalanqueeRepository::class)]
class Palanquee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dureePrevue = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dureeRealisee = null;

    #[ORM\Column(nullable: true)]
    private ?int $profondeurPrevue = null;

    #[ORM\Column(nullable: true)]
    private ?int $profondeurRealisee = null;

    #[ORM\ManyToOne(inversedBy: 'palanquees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'palanquees')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Moniteur $encadrant = null;

    /**
     * @var Collection<int, Plongeur>
     */
    #[ORM\ManyToMany(targetEntity: Plongeur::class, inversedBy: 'palanqueesParticipees')]
    private Collection $assigner;

    public function __construct()
    {
        $this->assigner = new ArrayCollection();
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

    public function getEncadrant(): ?Moniteur
    {
        return $this->encadrant;
    }

    public function setEncadrant(?Moniteur $encadrant): static
    {
        $this->encadrant = $encadrant;

        return $this;
    }

    /**
     * @return Collection<int, Plongeur>
     */
    public function getAssigner(): Collection
    {
        return $this->assigner;
    }

    public function addAssigner(Plongeur $assigner): static
    {
        if (!$this->assigner->contains($assigner)) {
            $this->assigner->add($assigner);
        }

        return $this;
    }

    public function removeAssigner(Plongeur $assigner): static
    {
        $this->assigner->removeElement($assigner);

        return $this;
    }
}
