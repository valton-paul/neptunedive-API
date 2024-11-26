<?php

namespace App\State\Plongeur;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Plongeur;
use App\Exception\AlreadyExistsException;
use App\Service\UtilisateurManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PlongeurPostProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UtilisateurManager $plongeurManager
    )
    {}

    /**
     * @throws AlreadyExistsException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $plain = $data->getPlainPassword();
        if ($plain !== null and $data instanceof Plongeur) {
            $this->plongeurManager->processNewUtilisateur($data, $plain);
            $data->eraseCredentials();
        }
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
