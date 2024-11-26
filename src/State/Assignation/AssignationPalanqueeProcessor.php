<?php

namespace App\State\Assignation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\AssignationPalanquee;
use App\Entity\InscriptionSortie;
use App\Repository\PalanqueeRepository;
use App\Repository\PlongeurRepository;
use App\Repository\SortieRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssignationPalanqueeProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private PlongeurRepository $plongeurRepository,
        private PalanqueeRepository $palanqueeRepository,
    )
    {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $plongeur = $this->plongeurRepository->find($uriVariables["idPlongeur"]);
        if(!$plongeur) {
            throw new NotFoundHttpException("Plongeur inexistant.");
        }
        $palanquee = $this->palanqueeRepository->find($uriVariables["idPalanquee"]);
        if(!$palanquee) {
            throw new NotFoundHttpException("Palanquee inexistante.");
        }
        if(!$data) {
            $data = new AssignationPalanquee();
        }
        $data->setPlongeur($plongeur);
        $data->setPalanquee($palanquee);
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
