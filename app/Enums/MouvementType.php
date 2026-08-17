<?php

namespace App\Enums;

enum MouvementType: string
{
    case ENTREE = 'entree';
    case SORTIE = 'sortie';

    public function label(): string
    {
        return match ($this) {
            self::ENTREE => 'Entrée',
            self::SORTIE => 'Sortie',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ENTREE => '📥',
            self::SORTIE => '📤',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::ENTREE => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::SORTIE => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        };
    }
}
