<?php

namespace App\Tests\Service;

use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Repository\PalanqueeRepository;
use App\Service\PalanqueeManager;
use App\Service\PalanqueeManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PalanqueeManagerTest extends TestCase
{

    private PalanqueeManagerInterface $palanqueeManager;
    private Plongeur $moniteur;
    private Sortie $sortie;
    private Plongeur $plongeur;
    private Palanquee $palanquee;
    private EntityManagerInterface $entityManager;
    private PalanqueeRepository $palanqueeRepository;

    protected function setUp(): void
    {
        $this->palanqueeRepository = $this->createMock(PalanqueeRepository::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->palanqueeManager = new PalanqueeManager(
            $this->entityManager,
        );
        $this->moniteur = (new Plongeur())
            ->setNiveauMoniteur(NiveauMoniteur::E4)
            ->setEmail("moniteur@gmail.com")
            ->setNom("Soulard")
            ->setFormation(false)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setPassword("Leny2000")
            ->setPrenom("Lény");
        $this->plongeur = (new Plongeur())
            ->setPrenom("Bob")
            ->setPassword("1234")
            ->setNom("Joon")
            ->setEmail("bobJoon@gmail.com")
            ->setFormation(false)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60);
        $this->palanquee = (new Palanquee())
            ->setEncadrant($this->moniteur)
            ->addAssigner($this->plongeur);
        $this->sortie = (new Sortie())
            ->setDateSortie(new \DateTime())
            ->setCreneau(Creneaux::APRES_MIDI)
            ->setLieu("")
            ->setType("piscine")
            ->setSecuriteSurface($this->moniteur)
            ->setDirecteurPlongee($this->moniteur);

        $this->palanquee = $this->palanquee->setSortie($this->sortie);
        $this->sortie = $this->sortie->addPalanquee($this->palanquee);

    }

    public function testCreatePalanqueeSuccess()
    {
        $this->palanqueeManager->create($this->sortie);
        $this->assertNotNull($this->palanquee->getId());
    }





}