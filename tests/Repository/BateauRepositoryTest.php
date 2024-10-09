<?php

namespace App\Tests\Repository;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BateauRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testFindAvailableBoats()
    {
        $bateau1 = new Bateau();
        $bateau1->setImmatriculation('BA001')->setNbPlaces(4);

        $bateau2 = new Bateau();
        $bateau2->setImmatriculation('BA002')->setNbPlaces(5);

        $bateau3 = new Bateau();
        $bateau3->setImmatriculation('BA003')->setNbPlaces(3);

        $this->entityManager->persist($bateau1);
        $this->entityManager->persist($bateau2);
        $this->entityManager->persist($bateau3);

        $moniteur = (new Plongeur())
            ->setNiveauMoniteur(NiveauMoniteur::E4)
            ->setEmail("moniteur@gmail.com")
            ->setNom("Charotte")
            ->setFormation(false)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setPassword("secure")
            ->setMoniteur(true)
            ->setPrenom("Thomas");

        $this->entityManager->persist($moniteur);

        $sortie1 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setLieu("")
            ->setType("piscine")
            ->setSecuriteSurface($moniteur)
            ->setDirecteurPlongee($moniteur)
            ->setBateau($bateau1);

        $sortie2 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setLieu("Palavas")
            ->setType("piscine")
            ->setSecuriteSurface($moniteur)
            ->setDirecteurPlongee($moniteur)
            ->setBateau($bateau2);

        $sortie3useless = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setLieu("Montepllier")
            ->setType("piscine")
            ->setSecuriteSurface($moniteur)
            ->setDirecteurPlongee($moniteur);

        $this->entityManager->persist($sortie1);
        $this->entityManager->persist($sortie2);
        $this->entityManager->persist($sortie3useless);

        $this->entityManager->flush();

        $bateauRepository = $this->entityManager->getRepository(Bateau::class);
        $availableBoats = $bateauRepository->findAvailableBoats(new \DateTime('2025-01-01'), Creneaux::MATIN);

        $this->assertCount(1, $availableBoats);
        $this->assertContains($bateau3, $availableBoats);
        $this->assertNotContains($bateau1, $availableBoats);
        $this->assertNotContains($bateau2, $availableBoats);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
