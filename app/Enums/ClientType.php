<?php

namespace App\Enums;

enum ClientType: string
{
    case PARTICULIER = 'particulier';
    case ENTREPRISE = 'entreprise';

    public function label(): string
    {
        return match ($this) {
            self::PARTICULIER => 'Particulier',
            self::ENTREPRISE => 'Entreprise',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PARTICULIER => '👤',
            self::ENTREPRISE => '🏢',
        };
    }
}
