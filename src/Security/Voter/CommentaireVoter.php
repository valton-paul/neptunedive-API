<?php

namespace App\Security\Voter;

use App\Entity\Commentaire;
use App\Entity\Plongeur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentaireVoter extends Voter
{
    public const MODIFIER = 'MODIFIER_COMMENTAIRE';
    public const SUPPRIMER = 'SUPPRIMER_COMMENTAIRE';
    public const ECRIRE = 'ECRIRE_COMMENTAIRE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::MODIFIER, self::SUPPRIMER, self::ECRIRE])
            && ($subject instanceof \App\Entity\Commentaire || ($subject == null && $attribute == self::ECRIRE));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::MODIFIER:
                if($user->getId() == $subject->getMoniteur()->getId()) return true;
                break;

            case self::SUPPRIMER:
                if($user->getId() == $subject->getMoniteur()->getId()) return true;
                break;

            case self::ECRIRE:
                return true;
        }

        return false;
    }
}
