<?php

namespace App\Service;

use App\Entity\Bateau;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\BateauRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BateauManager implements BateauManagerInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {}

    function create(string $immatriculation, int $nbPlace): Bateau
    {
        if($nbPlace < 0) throw new NotAllowedException('Place number can\' t be negative');

        $bateau = (new Bateau())
            ->setImmatriculation($immatriculation)
            ->setNbPlaces($nbPlace);

        $this->entityManager->persist($bateau);
        $this->entityManager->flush();

        return $bateau;
    }

    function delete(Bateau $bateau): void
    {
        $bateauExistant = $this->entityManager->getRepository(Bateau::class)->find($bateau->getId());

        if(!$bateauExistant) throw new NotFoundException();

        $this->entityManager->remove($bateauExistant);
        $this->entityManager->flush();
    }

    /**
     * @param \DateTime $date date of the event
     * @param Creneaux $creneaux moment of the day
     * @return Collection return list of boat available
     */
    function areAvailable(\DateTime $date, Creneaux $creneaux): Collection
    {
        return $this->entityManager->getRepository(Bateau::class)->findAvailableBoats($date, $creneaux);
    }
}