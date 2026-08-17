<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageGroupe extends Model
{
    use HasFactory;

    protected $table = 'message_groupe';

    protected $fillable = [
        'groupe_id',
        'expediteur_type',
        'expediteur_id',
        'contenu',
        'lu',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
        ];
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Groupe::class);
    }

    public function expediteur()
    {
        return match ($this->expediteur_type) {
            'artisan' => Artisan::find($this->expediteur_id),
            'client' => Client::find($this->expediteur_id),
            'equipe' => MembreEquipe::find($this->expediteur_id),
            default => null,
        };
    }

    public function getExpediteurNomAttribute(): string
    {
        $expediteur = $this->expediteur;

        if ($expediteur instanceof Artisan) {
            return $expediteur->user->name ?? 'Artisan';
        }
        if ($expediteur instanceof Client) {
            return $expediteur->nom ?? 'Client';
        }
        if ($expediteur instanceof MembreEquipe) {
            return $expediteur->nom ?? 'Membre';
        }

        return 'Inconnu';
    }
}
