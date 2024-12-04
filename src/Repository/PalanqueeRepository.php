<?php

namespace App\Repository;

use App\Entity\Palanquee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Palanquee>
 */
class PalanqueeRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Palanquee::class));
    }

    public function findPalanqueesWithoutCommentsForEncadrant(int $encadrantId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT sortie_id, ap.plongeur_id, encadrant_id,
                   pl.nom, pl.prenom,
                   duree_prevue, duree_realisee, profondeur_prevue,
                   profondeur_realisee, type, date_sortie, s.id_lieu_id, s.heure_sortie
            FROM palanquee p
            JOIN assignation_palanquee ap ON p.id = ap.palanquee_id
            JOIN sortie s on p.sortie_id = s.id
            JOIN plongeur pl on ap.plongeur_id = pl.id
            WHERE p.encadrant_id = :encadrantId 
              AND NOT EXISTS (
                SELECT 1 
                FROM commentaire c 
                WHERE c.plongeur_id = ap.plongeur_id 
                  AND c.sortie_id = p.sortie_id
            )
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['encadrantId' => $encadrantId]);

        return $resultSet->fetchAllAssociative(); // Renvoie un tableau associatif
    }

    public function findPalanqueesWithoutCommentsForAllEncadrant(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT sortie_id, ap.plongeur_id, encadrant_id,
                   pl.nom, pl.prenom,
                   duree_prevue, duree_realisee, profondeur_prevue,
                   profondeur_realisee, type, date_sortie, s.id_lieu_id, s.heure_sortie
            FROM palanquee p
            JOIN assignation_palanquee ap ON p.id = ap.palanquee_id
            JOIN sortie s on p.sortie_id = s.id
            JOIN plongeur pl on ap.plongeur_id = pl.id
            WHERE NOT EXISTS (
                SELECT 1 
                FROM commentaire c 
                WHERE c.plongeur_id = ap.plongeur_id 
                  AND c.sortie_id = p.sortie_id
            )
        ";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative(); // Renvoie un tableau associatif
    }

    //    /**
    //     * @return Palanquee[] Returns an array of Palanquee objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Palanquee
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
