<?php

namespace App\Tests\Service;

use App\Entity\Bateau;
use App\Entity\Sortie;
use App\Enum\Creneaux;
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
        $this->entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');    }

    protected function tearDown(): void
    {
        parent::tearDown();
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

    public function testFindAvailableBoats(): void
    {
        $bateau1 = (new Bateau())->setImmatriculation('BA001')->setNbPlaces(4);
        $bateau2 = (new Bateau())->setImmatriculation('BA002')->setNbPlaces(5);
        $bateau3 = (new Bateau())->setImmatriculation('BA003')->setNbPlaces(3);

        $this->entityManager->persist($bateau1);
        $this->entityManager->persist($bateau2);
        $this->entityManager->persist($bateau3);

        $sortie1 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setBateau($bateau1);

        $sortie2 = (new Sortie())
            ->setDateSortie(new \DateTime('2025-01-01'))
            ->setCreneau(Creneaux::MATIN)
            ->setBateau($bateau2);

        $this->entityManager->persist($sortie1);
        $this->entityManager->persist($sortie2);

        $this->entityManager->flush();

        $bateauRepository = $this->entityManager->getRepository(Bateau::class);
        $availableBoats = $bateauRepository->findAvailableBoats(new \DateTime('2025-01-01'), Creneaux::MATIN);

        // Assertions
        $this->assertCount(1, $availableBoats);
        $this->assertContains($bateau3, $availableBoats);
        $this->assertNotContains($bateau1, $availableBoats);
        $this->assertNotContains($bateau2, $availableBoats);
    }
}