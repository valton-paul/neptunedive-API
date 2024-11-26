<?php

namespace App\State\Assignation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\AssignationPalanqueeRepository;
use App\Repository\InscriptionSortieRepository;

class AssignationPalanqueeProvider implements ProviderInterface
{

    public function __construct(
        private AssignationPalanqueeRepository $assignationPalanqueeRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->assignationPalanqueeRepository->findOneBy(["plongeur" => $uriVariables["idPlongeur"],
            "palanquee" => $uriVariables["idPalanquee"]]);
    }
}
