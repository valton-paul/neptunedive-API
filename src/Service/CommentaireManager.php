<?php

namespace App\Service;

use App\Entity\Commentaire;
use App\Entity\Moniteur;
use App\Entity\Palanquee;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\CommentaireRepository;
use App\Service\CommentaireManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CommentaireManager implements CommentaireManagerInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


    /**
     * @inheritDoc
     */
    function create(Sortie $sortie, Plongeur $plongeur, Moniteur $moniteur, string $message): Commentaire
    {
        $commentaire = new Commentaire();
        $commentaire->setPlongeur($plongeur);
        $commentaire->setSortie($sortie);
        $commentaire->setMoniteur($moniteur);
        $commentaire->setMessage($message);
        $commentaire->setDateCommentaire($sortie->getDateSortie());
        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();
        return $commentaire;
    }

    function edit(Commentaire $commentaire, string $message): Commentaire
    {
        $commentaire->setMessage($message);
        $this->entityManager->flush();
        return $commentaire;
    }
}