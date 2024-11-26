<?php

namespace App\State\Sortie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;

class SortiePlongeursProvider implements ProviderInterface
{

    public function __construct(
        private InscriptionSortieRepository $inscriptionRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $inscriptions = $this->inscriptionRepository->findBy(["sortie" => $uriVariables["idSortie"]]);
        return array_map(fn($inscription) => $inscription->getPlongeur(), $inscriptions);
    }
}