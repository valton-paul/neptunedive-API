<?php

namespace App\State\Assignation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\AssignationPalanqueeRepository;
use App\Repository\PalanqueeRepository;

class AssignationPalanqueeSortieProvider implements ProviderInterface
{

    public function __construct(
        private PalanqueeRepository $palanqueeRepository,
        private AssignationPalanqueeRepository $assignationPalanqueeRepository,
    )
    {
    }



    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $palanquees = $this->palanqueeRepository->findBy(["sortie" => $uriVariables["idSortie"]]);
        return $this->assignationPalanqueeRepository->findBy(["palanquee" => $palanquees]);
    }
}
