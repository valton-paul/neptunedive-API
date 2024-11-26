<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class FinalizeInscriptionInput
{
    /**
     * @Assert\NotBlank(message="Le champ 'plainPassword' est requis.")
     */
    private ?string $plainPassword = null;

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
}
