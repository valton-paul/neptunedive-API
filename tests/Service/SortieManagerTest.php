<?php

namespace App\Tests\Service;

use App\Entity\Sortie;
use App\Entity\Plongeur;
use App\Enum\Creneaux;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Service\SortieManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;

class SortieManagerTest extends TestCase
{
    private SortieManager $sortieManager;
    private EntityManagerInterface $entityManager;
    private Security $security;
    private Plongeur $directeurPlongee;
    private Plongeur $plongeur;
    private Sortie $sortie;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(Security::class);

        $this->sortieManager = new SortieManager(
            $this->entityManager,
            $this->security
        );

        $this->plongeur = (new Plongeur())
            ->setPrenom("Bob")
            ->setNom("Lemon")
            ->setEmail("bob.l@gmail.com");

        $this->directeurPlongee = (new Plongeur())
            ->setPrenom("Lény")
            ->setNom("Soulard")
            ->setEmail("moniteur@gmail.com")
            ->setMoniteur(true);

        $this->sortie = (new Sortie())
            ->setDateSortie(new \DateTime('2025-10-12'))
            ->setDirecteurPlongee($this->directeurPlongee);
    }

    public function testSignUpForSuccess()
    {
        $this->sortieManager->signUpFor($this->directeurPlongee, $this->sortie);
        $this->assertContains($this->directeurPlongee, $this->sortie->getInscriptions());
    }

    public function testSignUpForAlreadyExistsException()
    {
        $this->sortie->addInscription($this->directeurPlongee);
        $this->expectException(AlreadyExistsException::class);
        $this->sortieManager->signUpFor($this->directeurPlongee, $this->sortie);
    }

    public function testQuitSuccess()
    {
        $this->sortieManager->signUpFor($this->plongeur, $this->sortie);
        $this->sortieManager->quit($this->plongeur, $this->sortie);
        $this->assertNotContains($this->plongeur, $this->sortie->getInscriptions());
    }

    public function testQuitThrowMemberNotFoundException()
    {
        $this->expectException(MemberNotFoundException::class);
        $this->sortieManager->quit($this->directeurPlongee, $this->sortie);
    }

    public function testQuitThrowNotAllowedException()
    {
        $this->sortieManager->signUpFor($this->directeurPlongee, $this->sortie);
        $this->expectException(NotAllowedException::class);
        $this->sortieManager->quit($this->directeurPlongee, $this->sortie);
    }

    public function testProcessNewSuccess()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(true);
        $this->security->method('getUser')->willReturn($this->directeurPlongee);

        $dateField = $this->createMock(FormInterface::class);
        $lieuField = $this->createMock(FormInterface::class);
        $typeField = $this->createMock(FormInterface::class);
        $creneauField = $this->createMock(FormInterface::class);
        $directeurPlongeeField = $this->createMock(FormInterface::class);
        $defaultField = $this->createMock(FormInterface::class); // Champ par défaut

        $dateField->method('getData')->willReturn(new \DateTime('2025-10-12'));
        $lieuField->method('getData')->willReturn('Brest');
        $typeField->method('getData')->willReturn('mer');
        $creneauField->method('getData')->willReturn(Creneaux::MATIN);
        $directeurPlongeeField->method('getData')->willReturn($this->directeurPlongee);

        $form->method('get')->willReturnCallback(function ($key) use (
            $dateField,
            $lieuField,
            $typeField,
            $creneauField,
            $directeurPlongeeField,
            $defaultField
        ) {
            return match ($key) {
                'dateSortie' => $dateField,
                'lieu' => $lieuField,
                'type' => $typeField,
                'creneau' => $creneauField,
                'directeurPlongee' => $directeurPlongeeField,
                default => $defaultField, // Retourne un mock par défaut
            };
        });

        $this->sortieManager->processNew($this->sortie, $form);
        $this->assertEquals(new \DateTime('2025-10-12'), $this->sortie->getDateSortie(), 'La date de la sortie devrait être 2025-10-12');
        $this->assertEquals('Brest', $this->sortie->getLieu(), 'Le lieu devrait être "Brest"');
        $this->assertEquals('mer', $this->sortie->getType(), 'Le type devrait être "mer"');
        $this->assertEquals(Creneaux::MATIN, $this->sortie->getCreneau(), 'Le créneau devrait être "matin"');
        $this->assertEquals($this->directeurPlongee, $this->sortie->getDirecteurPlongee(), 'Le directeur de plongée devrait être celui défini');
        $this->assertContains($this->directeurPlongee, $this->sortie->getInscriptions(), 'Le directeur de plongée devrait être inscrit à la sortie');
    }



    public function testProcessNewThrowNotAllowedException()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(false);

        $this->expectException(NotAllowedException::class);
        $this->sortieManager->processNew($this->sortie, $form);
    }

    public function testProcessEditSuccess()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(true);

        $dateField = $this->createMock(FormInterface::class);
        $lieuField = $this->createMock(FormInterface::class);
        $typeField = $this->createMock(FormInterface::class);
        $creneauField = $this->createMock(FormInterface::class);
        $directeurPlongeeField = $this->createMock(FormInterface::class);
        $defaultField = $this->createMock(FormInterface::class); // Champ par défaut

        $dateField->method('getData')->willReturn(new \DateTime('2025-10-12'));
        $lieuField->method('getData')->willReturn('Brest');
        $typeField->method('getData')->willReturn('mer');
        $creneauField->method('getData')->willReturn(Creneaux::MATIN);
        $directeurPlongeeField->method('getData')->willReturn($this->directeurPlongee);

        $form->method('get')->willReturnCallback(function ($key) use (
            $dateField,
            $lieuField,
            $typeField,
            $creneauField,
            $directeurPlongeeField,
            $defaultField
        ) {
            return match ($key) {
                'dateSortie' => $dateField,
                'lieu' => $lieuField,
                'type' => $typeField,
                'creneau' => $creneauField,
                'directeurPlongee' => $directeurPlongeeField,
                default => $defaultField, // Retourne un mock par défaut
            };
        });

        $this->sortieManager->processEdit($this->sortie, $form);
        $this->assertEquals(new \DateTime('2025-10-12'), $this->sortie->getDateSortie(), 'La date de la sortie devrait être 2025-10-12');
        $this->assertEquals('Brest', $this->sortie->getLieu(), 'Le lieu devrait être "Brest"');
        $this->assertEquals('mer', $this->sortie->getType(), 'Le type devrait être "mer"');
        $this->assertEquals(Creneaux::MATIN, $this->sortie->getCreneau(), 'Le créneau devrait être "matin"');
        $this->assertEquals($this->directeurPlongee, $this->sortie->getDirecteurPlongee(), 'Le directeur de plongée devrait être celui défini');
    }




    public function testProcessEditThrowNotAllowedException()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(false);

        $this->expectException(NotAllowedException::class);
        $this->sortieManager->processEdit($this->sortie, $form);
    }

    public function testCancelSuccess()
    {
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->sortie);
        $this->entityManager->expects($this->once())
            ->method('flush');
        $this->sortieManager->cancel($this->sortie);
    }


    public function testCancelThrowNotAllowedException()
    {
        $this->sortie->setDateSortie(new \DateTime('2023-10-03'));
        $this->expectException(NotAllowedException::class);
        $this->sortieManager->cancel($this->sortie);
    }
}
