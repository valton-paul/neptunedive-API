<?php

namespace App\State\Palanquee;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\AssignationPalanqueeRepository;

class PalanqueePlongeursProvider implements ProviderInterface
{
    public function __construct(
        private AssignationPalanqueeRepository $palanqueeRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $assignations = $this->palanqueeRepository->findBy(["palanquee" => $uriVariables["idPalanquee"]]);
        return array_map(fn($assignation) => $assignation->getPlongeur(), $assignations);
    }
}
