<?php

namespace App\EventListener;

use App\Entity\Commentaire;
use App\Entity\Palanquee;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\CommentaireRepository;
use App\Service\DatabaseContextResolver;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CommentaireListener
{

    private EntityRepository $commentaireRepository;

    public function __construct(
        private DatabaseContextResolver $resolver
    )
    {
        $this->commentaireRepository = $this->resolver->getRepository(Commentaire::class);
    }


    public function prePersist(LifecycleEventArgs $event): void
    {
//        $commentaire = $event->getObject();
//
//        // Vérifiez si l'entité est un Commentaire
//        if (!$commentaire instanceof Commentaire) {
//            return; // Si ce n'est pas un Commentaire, ne faites rien
//        }
//
//        $sortie = $commentaire->getSortie();
//        $moniteur = $commentaire->getMoniteur();
//        $plongeur = $commentaire->getPlongeur();
//
//        // Vérifie si le moniteur a bien encadré le plongeur lors de la sortie
//        if ($sortie->getPalanquees()->filter(
//                function (Palanquee $p) use ($moniteur, $plongeur) {
//                    return $p->getEncadrant() === $moniteur && $p->getAssigner()->contains($plongeur);
//                }
//            )->count() === 0) {
//            throw new NotAllowedException($moniteur->getEmail() . " hasn't supervised this diver.");
//        }
//        $possible_commentaire = $this->commentaireRepository->findOneBy([
//            'moniteur' => $moniteur->getId(),
//            'sortie' => $sortie->getId(),
//            'plongeur' => $plongeur->getId()
//        ]);
//        if ($possible_commentaire) {
//            throw new AlreadyExistsException();
//        }
    }

}