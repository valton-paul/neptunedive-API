<?php

namespace App\Tests\Service;

use App\Entity\Sortie;
use App\Entity\Plongeur;
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
            ->setPrenom("Lény")
            ->setNom("Soulard")
            ->setEmail("moniteur@gmail.com")
            ->setMoniteur(true);

        $this->sortie = (new Sortie())
            ->setDateSortie(new \DateTime('2025-10-12'))
            ->setDirecteurPlongee($this->plongeur);
    }

    public function testSignUpForSuccess()
    {
        $this->sortieManager->signUpFor($this->plongeur, $this->sortie);
        $this->assertContains($this->plongeur, $this->sortie->getInscriptions());
    }

    public function testSignUpForAlreadyExistsException()
    {
        $this->sortie->addInscription($this->plongeur); // Plongeur déjà inscrit
        $this->expectException(AlreadyExistsException::class);
        $this->sortieManager->signUpFor($this->plongeur, $this->sortie);
    }

    public function testQuitSuccess()
    {
        $this->sortie->addInscription($this->plongeur); // Plongeur déjà inscrit
        $this->sortieManager->quit($this->plongeur, $this->sortie);
        $this->assertNotContains($this->plongeur, $this->sortie->getInscriptions());
    }

    public function testQuitThrowMemberNotFoundException()
    {
        $this->expectException(MemberNotFoundException::class);
        $this->sortieManager->quit($this->plongeur, $this->sortie); // Plongeur non inscrit
    }

    public function testQuitThrowNotAllowedException()
    {
        $this->sortie->setDirecteurPlongee($this->plongeur); // Le plongeur est directeur de plongée
        $this->expectException(NotAllowedException::class);
        $this->sortieManager->quit($this->plongeur, $this->sortie);
    }

    public function testProcessNewSuccess()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(true); // ROLE_MONITEUR est accordé
        $this->security->method('getUser')->willReturn($this->plongeur);

        $form->method('get')->willReturnCallback(function ($key) {
            // Simule les données du formulaire
            return match ($key) {
                'dateSortie' => (new \DateTime('2025-10-12')),
                'lieu' => 'Brest',
                'type' => 'mer',
                'creneau' => 'matin',
                'directeurPlongee' => $this->plongeur,
                default => null,
            };
        });

        $this->sortieManager->processNew($this->sortie, $form);
        $this->assertContains($this->plongeur, $this->sortie->getInscriptions());
    }

    public function testProcessNewThrowNotAllowedException()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(false); // ROLE_MONITEUR refusé

        $this->expectException(NotAllowedException::class);
        $this->sortieManager->processNew($this->sortie, $form);
    }

    public function testProcessEditSuccess()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(true); // ROLE_MONITEUR est accordé

        $form->method('get')->willReturnCallback(function ($key) {
            return match ($key) {
                'dateSortie' => (new \DateTime('2025-10-12')),
                'lieu' => 'Brest',
                'type' => 'mer',
                'creneau' => 'matin',
                'directeurPlongee' => $this->plongeur,
                default => null,
            };
        });

        $this->sortieManager->processEdit($this->sortie, $form);
        $this->assertEquals('Brest', $this->sortie->getLieu());
    }

    public function testProcessEditThrowNotAllowedException()
    {
        $form = $this->createMock(FormInterface::class);
        $this->security->method('isGranted')->willReturn(false); // ROLE_MONITEUR refusé

        $this->expectException(NotAllowedException::class);
        $this->sortieManager->processEdit($this->sortie, $form);
    }

    public function testCancelSuccess()
    {
        $this->sortieManager->cancel($this->sortie);
        // Simuler la suppression via entityManager
        $this->entityManager->expects($this->once())->method('remove')->with($this->sortie);
        $this->entityManager->expects($this->once())->method('flush');
    }

    public function testCancelThrowNotAllowedException()
    {
        $this->sortie->setDateSortie(new \DateTime('2023-10-03')); // Sortie passée
        $this->expectException(NotAllowedException::class);
        $this->sortieManager->cancel($this->sortie);
    }
}
