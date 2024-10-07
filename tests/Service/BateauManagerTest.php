<?php

namespace App\Tests\Service;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\BateauRepository;
use App\Service\BateauManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BateauManagerTest extends KernelTestCase
{
    private $entityManager;
    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        if($this->entityManager === null){
            $this->entityManager = $this->createMock(EntityManagerInterface::class);
        }
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();

        $tables = $schemaManager->listTableNames();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $connection->executeStatement("TRUNCATE TABLE `$table`");
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testCreate(){

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Bateau::class));

        $bateauManager = new BateauManager($entityManagerMock);

        $bateau = $bateauManager->create('ABC123', 9);

        $this->assertInstanceOf(Bateau::class, $bateau);
        $this->assertEquals('ABC123', $bateau->getImmatriculation());
        $this->assertEquals(9, $bateau->getNbPlaces());
    }

    public function testCreateWithNegativePlaces(){
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $bateauManager = new BateauManager($entityManagerMock);

        $this->expectException(NotAllowedException::class);
        $bateauManager->create('ABC123', -1);
    }

    public function testDelete()
    {
        $bateauMock = $this->createMock(Bateau::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $bateauRepositoryMock = $this->createMock(BateauRepository::class);
        $bateauManager = new BateauManager($entityManagerMock);

        $entityManagerMock->method('getRepository')
            ->willReturn($bateauRepositoryMock);

        $bateauId = 1;
        $bateauMock->method('getId')->willReturn($bateauId);

        $bateauRepositoryMock->method('find')
            ->with($bateauId)
            ->willReturn($bateauMock);

        $entityManagerMock->expects($this->once())
            ->method('flush');

        $bateauManager->delete($bateauMock);
    }

    public function testDeleteOnNonExistentBoat(){
        $bateauMock = $this->createMock(Bateau::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $bateauRepositoryMock = $this->createMock(BateauRepository::class);
        $bateauManager = new BateauManager($entityManagerMock);

        $entityManagerMock->method('getRepository')
            ->willReturn($bateauRepositoryMock);

        $bateauId = 1;
        $bateauMock->method('getId')->willReturn($bateauId);

        $bateauRepositoryMock->method('find')
            ->with($bateauId)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);

        $bateauManager->delete($bateauMock);
    }

    public function testCorrectUpdate(){
        $bateau1 = (new Bateau())->setImmatriculation('BA001')->setNbPlaces(4);

        $this->entityManager->persist($bateau1);
        $this->entityManager->flush();

        $boat = ((new BateauManager($this->entityManager)))->update($bateau1, 5);

        $this->assertEquals(5, $boat->getNbPlaces());
        $this->assertEquals('BA001', $boat->getImmatriculation());

        $boat = ((new BateauManager($this->entityManager)))->update($bateau1, 10, 'AA000');

        $this->assertEquals(10, $boat->getNbPlaces());
        $this->assertEquals('AA000', $boat->getImmatriculation());
    }

    public function testUpdateWithNegativeNumber(){
        $bateau1 = (new Bateau())->setImmatriculation('BA001')->setNbPlaces(4);

        $this->entityManager->persist($bateau1);
        $this->entityManager->flush();

        $this->expectException(NotAllowedException::class);

        ((new BateauManager($this->entityManager)))->update($bateau1, -5);
    }

    public function testUpdateWithNonExistentBoat(){
        $bateau1 = (new Bateau())->setImmatriculation('BA001')->setNbPlaces(4);

        $this->entityManager->persist($bateau1);
        $this->entityManager->flush();

        $bateau2 = (new Bateau())->setImmatriculation('ZZ999')->setNbPlaces(4);

        $this->expectException(NotFoundException::class);

        ((new BateauManager($this->entityManager)))->update($bateau2, 4);
    }

    public function testFindAvailableBoats(): void
    {
        $bateau1 = (new Bateau())->setImmatriculation('BA001')->setNbPlaces(4);
        $bateau2 = (new Bateau())->setImmatriculation('BA002')->setNbPlaces(5);
        $bateau3 = (new Bateau())->setImmatriculation('BA003')->setNbPlaces(3);

        $this->entityManager->persist($bateau1);
        $this->entityManager->persist($bateau2);
        $this->entityManager->persist($bateau3);

        $directeurPlongee1 = (new Plongeur())
            ->setNiveauMoniteur(NiveauMoniteur::E4)
            ->setEmail("plongeur1@gmail.com")
            ->setNom("Charotte")
            ->setFormation(false)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setPassword("secure")
            ->setMoniteur(true)
            ->setPrenom("Thomas");;
        $directeurPlongee2 = (new Plongeur())
            ->setNiveauMoniteur(NiveauMoniteur::E4)
            ->setEmail("plongeur2@gmail.com")
            ->setNom("Charotte")
            ->setFormation(false)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setPassword("secure")
            ->setMoniteur(true)
            ->setPrenom("Thomas");;

        $this->entityManager->persist($directeurPlongee1);
        $this->entityManager->persist($directeurPlongee2);

        // Création des sorties
        $sortie1 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setType("mer")
            ->setLieu("Montpellier")
            ->setDirecteurPlongee($directeurPlongee1)
            ->setSecuriteSurface($directeurPlongee1)
            ->setBateau($bateau1);

        $sortie2 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setType("mer")
            ->setLieu("Palavas")
            ->setDirecteurPlongee($directeurPlongee2)
            ->setSecuriteSurface($directeurPlongee2)
            ->setBateau($bateau2);

        $this->entityManager->persist($sortie1);
        $this->entityManager->persist($sortie2);

        $this->entityManager->flush();

        $availableBoats = (new BateauManager($this->entityManager))->areAvailable(new \DateTime('2025-01-01'), Creneaux::MATIN);
        $this->assertCount(1, $availableBoats);
        $this->assertContains($bateau3, $availableBoats);
        $this->assertNotContains($bateau1, $availableBoats);
        $this->assertNotContains($bateau2, $availableBoats);
    }
}