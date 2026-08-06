<?php

namespace App\Models;

use App\Enums\EvenementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'chantier_id',
        'titre',
        'date_debut',
        'date_fin',
        'type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'type' => EvenementType::class,
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function getDureeAttribute(): int
    {
        return $this->date_debut->diffInMinutes($this->date_fin);
    }
}
