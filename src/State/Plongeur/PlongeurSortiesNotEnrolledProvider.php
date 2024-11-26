<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;
use App\Repository\SortieRepository;

class PlongeurSortiesNotEnrolledProvider implements ProviderInterface
{
    public function __construct(
        private InscriptionSortieRepository $inscriptionRepository,
        private SortieRepository $sortieRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($uriVariables["idPlongeur"])) {
            throw new \InvalidArgumentException("idPlongeur is missing in URI variables.");
        }
        $inscriptionsPlongeur = $this->inscriptionRepository->findBy(["plongeur" => $uriVariables["idPlongeur"]]);
        $inscriptionsPlongeur = array_filter($inscriptionsPlongeur, fn($inscription) => $inscription->getSortie()->getDateSortie() > new \DateTime());
        $sorties = $this->sortieRepository->findAll();
        $sorties = array_filter($sorties, fn($sortie) => $sortie->getDateSortie() > new \DateTime());
        $sortiesNotEnrolled = [];
        //A changer pour une requête SQL
        foreach ($sorties as $sortie) {
            $isEnrolled = false;
            foreach ($inscriptionsPlongeur as $inscription) {
                if ($inscription->getSortie()->getId() === $sortie->getId()) {
                    $isEnrolled = true;
                    break;
                }
            }
            if (!$isEnrolled) {
                $sortiesNotEnrolled[] = $sortie;
            }
        }
        return $sortiesNotEnrolled;
    }
}
