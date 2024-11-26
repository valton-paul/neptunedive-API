<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\AssignationPalanqueeRepository;

class PlongeurPalanqueesProvider implements ProviderInterface
{


    public function __construct(
        private AssignationPalanqueeRepository $assignationPalanqueeRepository,
    )
    {
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $assignations = $this->assignationPalanqueeRepository->findBy(["plongeur" => $uriVariables["idPlongeur"]]);
        return array_map(fn($assignation) => $assignation->getPalanquee(), $assignations);
    }
}
