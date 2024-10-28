<?php

namespace App\Service;

use App\Entity\Bateau;
use App\Enum\Creneaux;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\BateauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;

class BateauManager implements BateauManagerInterface
{
    private $entityManager;
    private BateauRepository $repository;

    public function __construct(
        private DatabaseContextResolver $provider,
    )
    {
        $this->entityManager = $this->provider->getEntityManager();
        $repo = $this->provider->getRepository(Bateau::class);
        if (!$repo instanceof BateauRepository) {
            throw new NotAllowedException("Repo should be bateau repository");
        }
        $this->repository = $repo;
    }

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
        $bateauExistant = $this->repository->find($bateau->getId());

        if(!$bateauExistant) throw new NotFoundException();

        $this->entityManager->remove($bateauExistant);
        $this->entityManager->flush();
    }

    function update(Bateau $bateau, int $newNbPlace = null, string $newImmatriculation = null): Bateau
    {
        $bateauExistant = $this->repository->find($bateau->getId());

        if(!$bateauExistant){
            throw new NotFoundException('Boat not found');
        }

        if ($newImmatriculation) {
            $bateauExistant->setImmatriculation($newImmatriculation);
        }

        if ($newNbPlace !== null) {
            if($newNbPlace < 0) throw new NotAllowedException('Place number can\'t be negative');
            $bateauExistant->setNbPlaces($newNbPlace);
        }

        $this->entityManager->flush();

        return $bateauExistant;
    }

    /**
     * @param \DateTime $date date of the event
     * @param Creneaux $creneaux moment of the day
     * @return Collection return list of boat available
     */
    function areAvailable(\DateTime $date, Creneaux $creneaux): array
    {
        return $this->repository->findAvailableBoats($date, $creneaux);
    }
}