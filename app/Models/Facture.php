<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Facture extends Model
{
    protected $fillable = [
        'chantier_id',
        'numero',
        'montant_ht',
        'tva',
        'montant_ttc',
        'date_emission',
        'date_echeance',
        'statut', // brouillon, envoyee, payee, annulee
        'description',
    ];

    protected function casts(): array
    {
        return [
            'montant_ht' => 'decimal:2',
            'tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'date_emission' => 'date',
            'date_echeance' => 'date',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }
}
