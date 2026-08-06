<?php

namespace App\Enums;

enum ChantierStatus: string
{
    case DEVIS = 'devis';
    case EN_COURS = 'en_cours';
    case ATTENTE = 'attente';
    case TERMINE = 'termine';
    case ARRET = 'arret';

    public function label(): string
    {
        return match ($this) {
            self::DEVIS => 'Devis accepté',
            self::EN_COURS => 'Chantier en cours',
            self::ATTENTE => 'En attente client',
            self::TERMINE => 'Terminé',
            self::ARRET => '🛑 En arrêt',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::DEVIS => 'bg-blue-50 text-blue-700',
            self::EN_COURS => 'bg-orange-50 text-orange-700',
            self::ATTENTE => 'bg-amber-50 text-amber-700',
            self::TERMINE => 'bg-emerald-50 text-emerald-700',
            self::ARRET => 'bg-red-50 text-red-700',
        };
    }

    public function dotColorClass(): string
    {
        return match ($this) {
            self::DEVIS => 'bg-blue-700',
            self::EN_COURS => 'bg-orange-700',
            self::ATTENTE => 'bg-amber-700',
            self::TERMINE => 'bg-emerald-700',
            self::ARRET => 'bg-red-700',
        };
    }
}
