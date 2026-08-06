<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'client_id',
        'membre_equipe_id',
        'sujet',
        'lu',
        'dernier_message_at',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
            'dernier_message_at' => 'datetime',
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function membreEquipe(): BelongsTo
    {
        return $this->belongsTo(MembreEquipe::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function getParticipantNameAttribute(): string
    {
        if ($this->client_id) {
            return $this->client->nom ?? 'Client';
        }
        if ($this->membre_equipe_id) {
            return $this->membreEquipe->nom ?? 'Membre';
        }

        return 'Inconnu';
    }
}
