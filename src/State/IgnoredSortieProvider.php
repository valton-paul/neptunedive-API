<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\IgnoredSortiesRepository;

class IgnoredSortieProvider implements ProviderInterface
{
    public function __construct(
        private IgnoredSortiesRepository $ignoredSortiesRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->ignoredSortiesRepository
            ->findOneBy(
                ["plongeur" => $uriVariables["idPlongeur"],
                    "sortie" => $uriVariables["idSortie"]]
            );
    }
}
