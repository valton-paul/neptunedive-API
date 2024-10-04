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
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Service\CommentaireManagerInterface;
use PHPUnit\Framework\TestCase;

class CommentaireManagerTest extends TestCase
{
    private CommentaireManagerInterface $commentaireManager;
    private Moniteur $moniteur;
    private Sortie $sortie;
    private Plongeur $plongeur;
    private Palanquee $palanquee;

    protected function setUp(): void
    {
        // Remplacez MockedCommentaireManager par l'implémentation réelle de CommentaireManagerInterface
        $this->commentaireManager = $this->createMock(CommentaireManagerInterface::class);
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
    }

    public function testCreateCommentaireThrowsAlreadyExistsException(): void
    {
        $message = "Commentaire déjà existant.";

        $this->commentaireManager->create(
            $this->sortie,
            $this->plongeur,
            $this->moniteur,
            $message);

        $this->expectException(AlreadyExistsException::class);

        $this->commentaireManager->create(
            $this->sortie,
            $this->plongeur,
            $this->moniteur,
            $message
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