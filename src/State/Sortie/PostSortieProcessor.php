<?php

namespace App\State\Sortie;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\InscriptionSortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PostSortieProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private EntityManagerInterface $entityManager
    ){}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $inscriptionSortie = new InscriptionSortie();
        $inscriptionSortie->setPlongeur($this->security->getUser());
        $inscriptionSortie->setSortie($data);
        $this->entityManager->persist($inscriptionSortie);
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
