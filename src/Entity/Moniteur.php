<?php

namespace App\Entity;

use App\Enum\NiveauMoniteur;
use App\Repository\MoniteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoniteurRepository::class)]
class Moniteur extends Plongeur
{

    #[ORM\Column(type: 'string', length: 25, enumType: NiveauMoniteur::class)]
    private ?NiveauMoniteur $niveauMoniteur = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'directeurPlongee')]
    private Collection $sortiesDP;

    /**
     * @var Collection<int, Palanquee>
     */
    #[ORM\OneToMany(targetEntity: Palanquee::class, mappedBy: 'encadrant')]
    private Collection $palanquees;

    public function __construct()
    {
        parent::__construct();
        $this->sortiesDP = new ArrayCollection();
        $this->palanquees = new ArrayCollection();
    }

    public function getNiveauMoniteur(): ?NiveauMoniteur
    {
        return $this->niveauMoniteur;
    }

    public function setNiveauMoniteur(NiveauMoniteur $niveauMoniteur): static
    {
        $this->niveauMoniteur = $niveauMoniteur;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesDP(): Collection
    {
        return $this->sortiesDP;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sortiesDP->contains($sorty)) {
            $this->sortiesDP->add($sorty);
            $sorty->setDirecteurPlongee($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sortiesDP->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getDirecteurPlongee() === $this) {
                $sorty->setDirecteurPlongee(null);
            }
        }

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
            $palanquee->setEncadrant($this);
        }

        return $this;
    }

    public function removePalanquee(Palanquee $palanquee): static
    {
        if ($this->palanquees->removeElement($palanquee)) {
            // set the owning side to null (unless already changed)
            if ($palanquee->getEncadrant() === $this) {
                $palanquee->setEncadrant(null);
            }
        }

        return $this;
    }
}
