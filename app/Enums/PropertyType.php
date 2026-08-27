<?php

namespace App\Enums;

enum PropertyType: string
{
    case Residential = 'residential';
    case Boutique = 'boutique';
    case Bureau = 'bureau';

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Bien résidentiel',
            self::Boutique => 'Boutique',
            self::Bureau => 'Bureau',
        };
    }
}
