<?php

namespace App\Service;

use App\Entity\Plongeur;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use phpDocumentor\Reflection\Types\ClassString;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DatabaseContextResolver
 * Permet d'appeler les bons repositories et entity manager
 */
class DatabaseContextResolver
{

    public function __construct(
        private RequestStack $requestStack,
        private ManagerRegistry $doctrine
    )
    {
    }

    /**
     * @param class-string $class
     * @return ObjectRepository
     */
    public function getRepository(string $class): EntityRepository
    {
        $club = $this->getClubFromRequest();
        return $this->doctrine->getRepository($class, $club);
    }

    public function getEntityManager(): EntityManager
    {
        $club = $this->getClubFromRequest();
        return $this->doctrine->getManager($club);
    }

    private function getClubFromRequest(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request->headers->get('CLUB') ?? 'default';
    }


}