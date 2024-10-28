<?php

// src/Security/UserProvider.php
namespace App\Security;

use App\Entity\Plongeur;
use App\Service\DatabaseContextResolver;
use PHPUnit\Util\Exception;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 * Permet de récupérer un utilisateur en fonction de son identifiant
 * en utilisant le service DatabaseContextResolver
 * pour assurer l'utilisation de la bonne base de données
 */
class UserProvider implements UserProviderInterface
{

    public function __construct(
        private DatabaseContextResolver $repositoryProvider,
    )
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->repositoryProvider
            ->getRepository(Plongeur::class)
            ->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Plongeur) {
            throw new UnsupportedUserException('Unsupported user type');
        }

        return $this->loadUserByIdentifier($user->getEmail());
    }

    public function supportsClass($class)
    {
        return is_a($class, Plongeur::class, true);
    }

}