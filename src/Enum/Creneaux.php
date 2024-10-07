<?php

namespace App\Enum;

enum Creneaux: string
{

    case MATIN = "MATIN";
    case APRES_MIDI = "APRES-MIDI";
    case NUIT = "NUIT";

    public function label(): string
    {
        return match ($this) {
            self::MATIN => 'Matin',
            self::APRES_MIDI => 'Après-midi',
            self::NUIT => 'Nuit',
        };
    }

}
