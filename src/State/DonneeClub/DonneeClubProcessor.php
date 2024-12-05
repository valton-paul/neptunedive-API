<?php

namespace App\State\DonneeClub;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DonneeClub;
use App\Service\DonneeClubManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DonneeClubProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private DonneeClubManagerInterface $donneeClubManager
    )
    {}

    /**

    @throws AlreadyExistsException*/
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed{
        if ($data instanceof DonneeClub && $data->getPlainPasswordSpond() !== null)
        {
            $password = $this->donneeClubManager->encryptPassword($data->getPlainPasswordSpond());
            $data->setPasswordSpond($password);
            $data->eraseCredentials();
        }

        // Déléguez au processeur de persistance
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
