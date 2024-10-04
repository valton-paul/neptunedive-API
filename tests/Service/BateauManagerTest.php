<?php

namespace App\Tests\Service;

use App\Entity\Bateau;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\BateauRepository;
use App\Service\BateauManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BateauManagerTest extends TestCase
{
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

}