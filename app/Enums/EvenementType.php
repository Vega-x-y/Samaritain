<?php

namespace App\Enums;

enum EvenementType: string
{
    case INTERVENTION = 'intervention';
    case REUNION = 'reunion';
    case DEPLACEMENT = 'deplacement';

    public function label(): string
    {
        return match ($this) {
            self::INTERVENTION => 'Intervention',
            self::REUNION => 'Réunion',
            self::DEPLACEMENT => 'Déplacement',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::INTERVENTION => '🔧',
            self::REUNION => '👥',
            self::DEPLACEMENT => '🚗',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::INTERVENTION => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            self::REUNION => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
            self::DEPLACEMENT => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        };
    }
}
