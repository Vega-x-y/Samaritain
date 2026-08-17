<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devis extends Model
{
    protected $fillable = [
        'chantier_id',
        'numero',
        'statut', // brouillon, envoye, signe
        'date_envoi',
        'date_signature',
        'montant_ht',
        'tva',
        'montant_ttc',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant_ht' => 'decimal:2',
            'tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'date_envoi' => 'date',
            'date_signature' => 'date',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }
}
