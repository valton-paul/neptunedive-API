<?php

namespace App\EventListener;

use App\Entity\Plongeur;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PlongeurListener
{
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $plongeur = $event->getObject();
        if (!$plongeur instanceof Plongeur) {
            return;
        }

        if ($event->hasChangedField('isMoniteur')) {
            $this->editRoles($plongeur, $event->getNewValue('isMoniteur'));
        } else if ($event->hasChangedField('role')) {
            $roles = $event->getNewValue('role');
            $this->editMoniteurFromRole($plongeur, $roles);
        }
    }

    private function editRoles(Plongeur $plongeur, bool $newValue) {
        $newValue ? $plongeur->addRole('ROLE_MONITEUR') : $plongeur->removeRole('ROLE_MONITEUR');
    }

    private function editMoniteurFromRole(Plongeur $plongeur, ArrayCollection $new_role) {
        if ($new_role->contains("ROLE_MONITEUR")) {
            $plongeur->removeRole('ROLE_MONITEUR');
        } else {
            $plongeur->addRole('ROLE_MONITEUR');
        }
    }
}