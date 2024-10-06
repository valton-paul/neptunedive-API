<?php

namespace App\Entity;

use App\Enum\Creneaux;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateSortie = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'string', length: 50, enumType: Creneaux::class)]
    private ?Creneaux $creneau = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plongeur $directeurPlongee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plongeur $securiteSurface = null;

    /**
     * @var Collection<int, Palanquee>
     */
    #[ORM\OneToMany(targetEntity: Palanquee::class, mappedBy: 'sortie')]
    private Collection $palanquees;

    /**
     * @var Collection<int, Plongeur>
     */
    #[ORM\ManyToMany(targetEntity: Plongeur::class, inversedBy: 'sorties')]
    private Collection $inscriptions;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Bateau $bateau = null;

    #[ORM\ManyToOne]
    private ?Plongeur $piloteBateau = null;

    public function __construct()
    {
        $this->palanquees = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(\DateTimeInterface $dateSortie): static
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getCreneau(): ?Creneaux
    {
        return $this->creneau;
    }

    public function setCreneau(Creneaux $creneau): static
    {
        $this->creneau = $creneau;

        return $this;
    }

    public function getDirecteurPlongee(): ?Plongeur
    {
        return $this->directeurPlongee;
    }

    public function setDirecteurPlongee(?Plongeur $directeurPlongee): static
    {
        $this->directeurPlongee = $directeurPlongee;

        return $this;
    }

    public function getSecuriteSurface(): ?Plongeur
    {
        return $this->securiteSurface;
    }

    public function setSecuriteSurface(?Plongeur $securiteSurface): static
    {
        $this->securiteSurface = $securiteSurface;

        return $this;
    }

    /**
     * @return Collection<int, Palanquee>
     */
    public function getPalanquees(): Collection
    {
        return $this->palanquees;
    }

    public function addPalanquee(Palanquee $palanquee): static
    {
        if (!$this->palanquees->contains($palanquee)) {
            $this->palanquees->add($palanquee);
            $palanquee->setSortie($this);
        }

        return $this;
    }

    public function removePalanquee(Palanquee $palanquee): static
    {
        if ($this->palanquees->removeElement($palanquee)) {
            // set the owning side to null (unless already changed)
            if ($palanquee->getSortie() === $this) {
                $palanquee->setSortie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plongeur>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Plongeur $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
        }

        return $this;
    }

    public function removeInscription(Plongeur $inscription): static
    {
        $this->inscriptions->removeElement($inscription);

        return $this;
    }

    public function getBateau(): ?Bateau
    {
        return $this->bateau;
    }

    public function setBateau(?Bateau $bateau): static
    {
        $this->bateau = $bateau;

        return $this;
    }

    public function getPiloteBateau(): ?Plongeur
    {
        return $this->piloteBateau;
    }

    public function setPiloteBateau(?Plongeur $piloteBateau): static
    {
        $this->piloteBateau = $piloteBateau;

        return $this;
    }
}
