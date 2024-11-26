<?php

namespace App\State\Inscription;



use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;


class InscriptionSortieProvider implements ProviderInterface
{
    public function __construct(
        private InscriptionSortieRepository $inscriptionRepository,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->inscriptionRepository->findOneBy(["plongeur" => $uriVariables["idPlongeur"],
            "sortie" => $uriVariables["idSortie"]]);
    }
}