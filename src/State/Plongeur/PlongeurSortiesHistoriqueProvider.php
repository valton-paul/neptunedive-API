<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;
use DateTime;

class PlongeurSortiesHistoriqueProvider implements ProviderInterface
{

    public function __construct(
        private InscriptionSortieRepository $inscriptionRepository,
    )
    {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $now = new DateTime();

        $queryBuilder = $this->inscriptionRepository->createQueryBuilder('i')
            ->join('i.sortie', 's')
            ->where('i.plongeur = :idPlongeur')
            ->andWhere('s.dateSortie < :now')
            ->setParameter('idPlongeur', $uriVariables['idPlongeur'])
            ->setParameter('now', $now);

        $inscriptions = $queryBuilder->getQuery()->getResult();

        return array_map(fn($inscription) => $inscription->getSortie(), $inscriptions);
    }
}