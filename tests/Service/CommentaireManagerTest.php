<?php

namespace App\Tests\Service;

use App\Entity\Commentaire;
use App\Entity\Moniteur;
use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use App\EventListener\CommentaireListener;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\CommentaireRepository;
use App\Service\CommentaireManager;
use App\Service\CommentaireManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;

class CommentaireManagerTest extends TestCase
{
    private CommentaireManagerInterface $commentaireManager;
    private Moniteur $moniteur;
    private Sortie $sortie;
    private Plongeur $plongeur;
    private Palanquee $palanquee;
    private EntityManagerInterface $entityManager;
    private CommentaireRepository $commentaireRepository;
    private CommentaireListener $commentaireListener;

    protected function setUp(): void
    {
        $this->commentaireRepository = $this->createMock(CommentaireRepository::class);

        // Instanciation du listener avec le repository mocké
        $this->commentaireListener = new CommentaireListener($this->commentaireRepository);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        // Remplacez MockedCommentaireManager par l'implémentation réelle de CommentaireManagerInterface
        $this->commentaireManager = new CommentaireManager(
            $this->entityManager,
        );
        $this->moniteur = (new Moniteur())
            ->setNiveauMoniteur(NiveauMoniteur::E4)
            ->setEmail("moniteur@gmail.com")
            ->setNom("Charotte")
            ->setFormation(false)
            ->setNiveauPlongeeAutonome(NiveauPlongeeAutonome::PA60)
            ->setNiveauPlongeeEncadre(NiveauPlongeeEncadre::PE60)
            ->setPassword("secure")
            ->setPrenom("Thomas");
        $this->plongeur = (new Plongeur())
            ->setPrenom("Tommy")
            ->setPassword("1234")
            ->setNom("Charrote")
            ->setEmail("charrote@gmail.com")
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

    public function testCreateCommentaireSuccess(): void
    {
        $message = "Message";
        $commentaire = $this->commentaireManager->create(
            $this->sortie,
            $this->plongeur,
            $this->moniteur,
            $message);
        $this->assertEquals(
            $this->sortie->getDateSortie()->format("d/m/Y"),
            $commentaire->getDateCommentaire()->format("d/m/Y")
        );
        $this->assertEquals(
            $this->sortie,
            $commentaire->getSortie(),
        );
        $this->assertEquals(
            $this->plongeur,
            $commentaire->getPlongeur(),
        );
        $this->assertEquals(
            $this->moniteur,
            $commentaire->getMoniteur(),
        );
    }

    public function testCreateCommentaireThrowsAlreadyExistsException(): void
    {
        $message = "Commentaire déjà existant.";

        $commentaire = $this->commentaireManager->create(
            $this->sortie,
            $this->plongeur,
            $this->moniteur,
            $message);

        $this->expectException(AlreadyExistsException::class);
        $this->commentaireRepository
            ->method('findOneBy')
            ->willReturn($commentaire); // Simule un commentaire déjà existant

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->method('getObject')->willReturn($commentaire);

        $this->commentaireListener->prePersist(
            $lifeCycleEventArgs
        );
    }

    public function testCreateCommentaireThrowsNotAllowedException(): void
    {
        $message = "Commentaire not allowed";
        $fake_sortie = $this->createMock(Sortie::class);
        $fake_sortie->method('getDateSortie')->willReturn(new \DateTime());
        $fake_sortie->method('getPalanquees')->willReturn(new ArrayCollection());
        $commentaire = $this->commentaireManager->create(
            $fake_sortie,
            $this->plongeur,
            $this->moniteur,
            $message);

        $this->expectException(NotAllowedException::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->method('getObject')->willReturn($commentaire);

        $this->commentaireListener->prePersist(
            $lifeCycleEventArgs
        );
    }

    public function testCreateCommentaireThrowsNotAllowedException_SupervisingButNoStudent(): void
    {
        $message = "Commentaire not allowed";
        $fake_sortie = $this->createMock(Sortie::class);
        $fake_sortie->method('getDateSortie')->willReturn(new \DateTime());

        $palanquees = new ArrayCollection();
        $palanquees->add((new Palanquee())->setEncadrant($this->moniteur));

        $fake_sortie->method('getPalanquees')->willReturn($palanquees);
        $commentaire = $this->commentaireManager->create(
            $fake_sortie,
            $this->plongeur,
            $this->moniteur,
            $message);

        $this->expectException(NotAllowedException::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->method('getObject')->willReturn($commentaire);

        $this->commentaireListener->prePersist(
            $lifeCycleEventArgs
        );
    }

    public function testCreateCommentaireThrowsNotAllowedException_StudentButNoSupervising(): void
    {
        $message = "Commentaire not allowed";
        $fake_sortie = $this->createMock(Sortie::class);
        $fake_sortie->method('getDateSortie')->willReturn(new \DateTime());

        $palanquees = new ArrayCollection();
        $palanquees->add((new Palanquee())->addAssigner($this->plongeur));

        $fake_sortie->method('getPalanquees')->willReturn($palanquees);
        $commentaire = $this->commentaireManager->create(
            $fake_sortie,
            $this->plongeur,
            $this->moniteur,
            $message);

        $this->expectException(NotAllowedException::class);
        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->method('getObject')->willReturn($commentaire);

        $this->commentaireListener->prePersist(
            $lifeCycleEventArgs
        );
    }

    public function testEditCommentaireSuccess(): void
    {
        $commentaire = new Commentaire();
        $message = "Modification du commentaire.";
        $commentaire = $commentaire
            ->setDateCommentaire(new \DateTime())
            ->setMessage("Initial message");
        $result = $this->commentaireManager->edit($commentaire, $message);
        $this->assertEquals($message, $result->getMessage());
    }
}