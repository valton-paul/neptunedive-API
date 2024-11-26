<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;

class PlongeurSortiesProvider implements ProviderInterface
{

    public function __construct(
        private InscriptionSortieRepository $inscriptionRepository,
    )
    {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $inscriptions = $this->inscriptionRepository->findBy(["plongeur" => $uriVariables["idPlongeur"]]);
        return array_map(fn($inscription) => $inscription->getSortie(), $inscriptions);
    }
}