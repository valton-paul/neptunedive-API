<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\PlongeurRepository;

class EncadrantPlongeursNoCommentsProvider implements ProviderInterface
{


    public function __construct(
        private PlongeurRepository $plongeurRepository,
    )
    {
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $encadrant = $this->plongeurRepository->find($uriVariables["idEncadrant"]);
        return $this->plongeurRepository->findPlongeursWithNoCommentsByMonitor($encadrant);
    }
}