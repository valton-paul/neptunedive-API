<?php

namespace App\Tests\Service;

use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Repository\PlongeurRepository;
use App\Service\UtilisateurManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UtilisateurManagerTest extends TestCase
{

    private Plongeur $plongeur;
    private UtilisateurManager $utilisateurManager;

    protected function setUp(): void
    {
        $this->plongeur = new Plongeur();
        $this->plongeur
            ->setPrenom("Tommy")
            ->setPassword("1234")
            ->setNom("Charrote")
            ->setEmail("charrote@gmail.com")
            ->setFormation(false)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60);
        $this->utilisateurManager = new UtilisateurManager(
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(PlongeurRepository::class),
            $this->createMock(EntityManager::class)
        );
    }

    public function testMakeSupervisorSuccessful(): void
    {
        $this->utilisateurManager->makeSupervisor($this->plongeur, NiveauMoniteur::E3);
        $this->assertTrue($this->plongeur->isMoniteur());
        $this->assertEquals(NiveauMoniteur::E3, $this->plongeur->getNiveauMoniteur());
    }

}