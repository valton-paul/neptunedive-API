<?php

namespace App\State\Inscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\InscriptionSortie;
use App\Repository\PlongeurRepository;
use App\Repository\SortieRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InscriptionSortieProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private PlongeurRepository $plongeurRepository,
        private SortieRepository $sortieRepository,
    )
    {}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $plongeur = $this->plongeurRepository->find($uriVariables["idPlongeur"]);
        if(!$plongeur) {
            throw new NotFoundHttpException("Plongeur inexistant.");
        }
        $sortie = $this->sortieRepository->find($uriVariables["idSortie"]);
        if(!$sortie) {
            throw new NotFoundHttpException("Sortie inexistante.");
        }
        if(!$data) {
            $data = new InscriptionSortie();
        }
        $data->setPlongeur($plongeur);
        $data->setSortie($sortie);
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
