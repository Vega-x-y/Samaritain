<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'expediteur_type',
        'expediteur_id',
        'contenu',
        'type',
        'metadata',
        'lu',
        'fichier_path',
        'fichier_nom',
        'fichier_mime',
        'fichier_taille',
        'document_id',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'fichier_taille' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function getExpediteurAttribute()
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
