<?php

namespace App\Enums;

enum MembreStatut: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';

    public function label(): string
    {
        return match ($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::ACTIF => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::INACTIF => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        };
    }
}
