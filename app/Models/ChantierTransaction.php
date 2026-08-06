<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChantierTransaction extends Model
{
    protected $fillable = [
        'chantier_id',
        'type', // acompte, solde, remboursement
        'montant',
        'date',
        'statut', // en_attente, recu, rembourse
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }
}
