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
use PHPUnit\Framework\TestCase;

class BateauManagerTest extends TestCase
{
    private $entityManagerMock;
    private $bateauRepositoryMock;
    private $bateauManager;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->bateauRepositoryMock = $this->createMock(BateauRepository::class);

        $this->entityManagerMock
            ->method('getRepository')
            ->willReturn($this->bateauRepositoryMock);

        $this->bateauManager = new BateauManager($this->entityManagerMock);
    }

    public function testCreate()
    {
        $bateau = new Bateau();
        $bateau->setImmatriculation('ABC123')->setNbPlaces(9);

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Bateau::class));

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $bateauManager = new BateauManager($this->entityManagerMock);
        $createdBateau = $bateauManager->create('ABC123', 9);

        $this->assertInstanceOf(Bateau::class, $createdBateau);
        $this->assertEquals('ABC123', $createdBateau->getImmatriculation());
        $this->assertEquals(9, $createdBateau->getNbPlaces());
    }

    public function testCreateWithNegativePlaces()
    {
        $bateauManager = new BateauManager($this->entityManagerMock);

        $this->expectException(NotAllowedException::class);

        $bateauManager->create('ABC123', -1);
    }

    public function testDelete()
    {
        $bateauMock = $this->createMock(Bateau::class);
        $bateauMock->method('getId')->willReturn(1);

        $this->bateauRepositoryMock->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($bateauMock);

        $this->entityManagerMock->expects($this->once())
            ->method('remove')
            ->with($bateauMock);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->bateauManager->delete($bateauMock);
    }

    public function testDeleteOnNonExistentBoat()
    {
        $this->bateauRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->bateauRepositoryMock);

        $bateauManager = new BateauManager($this->entityManagerMock);

        $this->expectException(NotFoundException::class);

        $bateauManager->delete(new Bateau());
    }

    public function testCorrectUpdate()
    {
        $bateau = (new Bateau())->setImmatriculation('BB000')->setNbPlaces(5);

        $this->bateauRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($bateau);

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $updatedBateau = $this->bateauManager->update($bateau, 10, 'AA000');

        $this->assertEquals(10, $updatedBateau->getNbPlaces());
        $this->assertEquals('AA000', $updatedBateau->getImmatriculation());
    }


    public function testUpdateWithNegativeNumber()
    {
        $bateau = (new Bateau())->setImmatriculation('BB000')->setNbPlaces(5);

        $this->bateauRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($bateau);

        $bateauManager = new BateauManager($this->entityManagerMock);

        $this->expectException(NotAllowedException::class);

        $bateauManager->update($bateau, -5);
    }

    public function testUpdateWithNonExistentBoat()
    {
        $bateau2 = new Bateau();
        $bateau2->setImmatriculation('ZZ999')->setNbPlaces(4);

        $bateauManager = new BateauManager($this->entityManagerMock);

        $this->expectException(NotFoundException::class);
        $bateauManager->update($bateau2, 6);
    }
}