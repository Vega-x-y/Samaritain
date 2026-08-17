<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'chantier_id',
        'categorie', // materiaux, main_oeuvre, transport, autre
        'montant',
        'date',
        'justificatif', // path vers le fichier
        'description',
        'fournisseur',
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
