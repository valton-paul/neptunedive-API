<?php

namespace App\Repository;

use App\Entity\InscriptionSortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InscriptionSortie>
 */
class InscriptionSortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionSortie::class);
    }

    public function findInscriptionsWherePlongeurNotAssignedBySortieId(int $sortieId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.sortie = :sortieId')
            ->setParameter('sortieId', $sortieId)
            ->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\AssignationPalanquee a
                                            JOIN a.palanquee p
                                            WHERE a.plongeur = i.plongeur
                                            AND p.sortie = i.sortie)')
            ->andWhere('NOT EXISTS (SELECT 1 FROM App\Entity\Palanquee pa
                                            WHERE pa.encadrant = i.plongeur
                                            AND pa.sortie = i.sortie)')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return InscriptionSortie[] Returns an array of InscriptionSortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InscriptionSortie
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
