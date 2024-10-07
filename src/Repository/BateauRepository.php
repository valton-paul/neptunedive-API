<?php

namespace App\Repository;

use App\Entity\Bateau;
use App\Enum\Creneaux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bateau>
 */
class BateauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bateau::class);
    }

    public function findAllBoatsWithSorties() {
        $query = $this->createQueryBuilder('b');

        $query->leftJoin('b.sortie', 's')
            ->addSelect('s');

        return $query->getQuery()->getResult();
    }

    public function findAvailableBoats(\DateTime $date, Creneaux $creneaux) :array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('App\Entity\Sortie', 's', 'WITH', 's.bateau = b')
            ->where('s IS NULL OR (s.dateSortie != :date OR s.creneau != :creneau)')
            ->setParameter('date', $date)
            ->setParameter('creneau', $creneaux)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Bateau[] Returns an array of Bateau objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Bateau
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
