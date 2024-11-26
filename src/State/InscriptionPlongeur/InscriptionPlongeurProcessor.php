<?php

namespace App\State\InscriptionPlongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\FinalizeInscriptionInput;
use App\Exception\NotAllowedException;
use App\Repository\InscriptionPlongeurRepository;
use App\Service\InscriptionPlongeurManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class InscriptionPlongeurProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface         $persistProcessor,
        private InscriptionPlongeurManager $inscriptionPlongeurManager,
        private InscriptionPlongeurRepository $inscriptionPlongeurRepository
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation->getName() === 'finalize_inscription') {
            if (!$data instanceof FinalizeInscriptionInput) {
                throw new \InvalidArgumentException('Le champ "plainPassword" est requis pour finaliser l\'inscription.');
            }

            $plainPassword = $data->getPlainPassword();

            $idInscription = $uriVariables['idInscription'];
            $inscriptionPlongeur = $this->inscriptionPlongeurRepository->find($idInscription);
            if (!$inscriptionPlongeur) {
                throw new NotAllowedException('Inscription non trouvée.');
            }

            $plongeur = $this->inscriptionPlongeurManager->createPlongeurFromDemande($inscriptionPlongeur, $plainPassword);

            return $plongeur;
        }

        if ($operation->getName() == 'validate_inscription') {
            $this->inscriptionPlongeurManager->validateInscription($data);
        }

        if ($operation->getName() == 'create_inscription') {
            $this->inscriptionPlongeurManager->processNewInscription($data);
        }
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
