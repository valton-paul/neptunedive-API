<?php

namespace App\Tests\Service;

use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
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
            ->setPrenom("Lény")
            ->setMoniteur(true);
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
            ->setDateSortie(new \DateTime('12/10/2025'))
            ->setCreneau(Creneaux::APRES_MIDI)
            ->setLieu("")
            ->setType("piscine")
            ->setSecuriteSurface($this->moniteur)
            ->setDirecteurPlongee($this->moniteur);

        $this->palanquee = $this->palanquee->setSortie($this->sortie);
        $this->sortie = $this->sortie->addPalanquee($this->palanquee);
        $this->sortie = $this->sortie->addInscription($this->moniteur);
        $this->sortie = $this->sortie->addInscription($this->plongeur);
    }

    public function testCreatePalanqueeSuccess()
    {
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->assertEquals($this->sortie, $palanquee->getSortie());
    }

    public function testCreatePalanqueeThrowNotAllowedException(){
        $this->expectException(NotAllowedException::class);
        $this->sortie->setDateSortie(new \DateTime('03/10/2023'));
        $this->palanqueeManager->create($this->sortie);
    }

    public function testRemovePalaquee(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->assertNotNull($palanquee);
        $this->palanqueeManager->remove($palanquee);
        $this->assertNotEquals($this->sortie, $palanquee->getSortie());
    }

    public function testRemovePalanqueeThrowNotAllowedException(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->sortie->setDateSortie(new \DateTime('03/10/2023'));
        $this->expectException(NotAllowedException::class);
        $this->palanqueeManager->remove($palanquee);
    }

    public function testAssignAsSupervisorSuccess(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsSupervisor($this->moniteur, $palanquee);
        $this->assertEquals($this->moniteur, $palanquee->getEncadrant());
    }

    public function testAssignAsSuprevisorSuccessSupervisorIsDiver(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsDiver($this->moniteur, $palanquee);
        $this->assertContains($this->moniteur, $palanquee->getAssigner());
        $this->palanqueeManager->assignAsSupervisor($this->moniteur, $palanquee);
        $this->assertEquals($this->moniteur, $palanquee->getEncadrant());
    }

    public function testAssignAsSupervisorThrowMemberNotFoundExceptionNotMoniteur(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $newMoniteur = new Plongeur();
        $this->expectException(MemberNotFoundException::class);
        $this->palanqueeManager->assignAsSupervisor($newMoniteur, $palanquee);
    }

    public function testAssignAsSupervisorThrowMemberNotFoundExceptionIsMoniteur(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $newMoniteur = new Plongeur();
        $newMoniteur->setMoniteur(true);
        $this->expectException(MemberNotFoundException::class);
        $this->palanqueeManager->assignAsSupervisor($newMoniteur, $palanquee);
    }

    public function testAssignAsSupervisorThrowNotAllowedException()
    {
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $newMoniteur = new Plongeur();
        $this->sortie->addInscription($newMoniteur);
        $this->expectException(NotAllowedException::class);
        $this->palanqueeManager->assignAsSupervisor($newMoniteur, $palanquee);
    }

    public function testAssignAsDiverSuccess(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsDiver($this->plongeur, $palanquee);
        $this->assertContains($this->plongeur, $palanquee->getAssigner());
    }

    public function testAssignAsDiverSuccessDiverIsSupervisor(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsSupervisor($this->moniteur, $palanquee);
        $this->assertEquals($this->moniteur, $palanquee->getEncadrant());
        $this->palanqueeManager->assignAsDiver($this->moniteur, $palanquee);
        $this->assertContains($this->moniteur, $palanquee->getAssigner());
    }


    public function testAssignAsDiverThrowMemberNotFoundException(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $plongeur = new Plongeur();
        $this->expectException(MemberNotFoundException::class);
        $this->palanqueeManager->assignAsDiver($plongeur, $palanquee);
    }

    public function testKickCurrentSupervisorSuccess(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsSupervisor($this->moniteur, $palanquee);
        $this->assertEquals($this->moniteur, $palanquee->getEncadrant());
        $this->palanqueeManager->kickCurrentSupervisor($palanquee);
        $this->assertNotEquals($palanquee->getEncadrant(), $this->moniteur);
    }

    public function testKickCurrentSupervisorThrowMemberNotFoundException(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->expectException(MemberNotFoundException::class);
        $this->palanqueeManager->kickCurrentSupervisor($palanquee);
    }

    public function testKickSuccess(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->palanqueeManager->assignAsDiver($this->plongeur, $palanquee);
        $this->assertContains($this->plongeur, $palanquee->getAssigner());
        $this->palanqueeManager->kick($this->plongeur, $palanquee);
        $this->assertNotContains($this->plongeur, $palanquee->getAssigner());
    }

    public function testKickThrowMemberNotFoundException(){
        $palanquee = $this->palanqueeManager->create($this->sortie);
        $this->expectException(MemberNotFoundException::class);
        $this->palanqueeManager->kick($this->plongeur, $palanquee);
    }
}