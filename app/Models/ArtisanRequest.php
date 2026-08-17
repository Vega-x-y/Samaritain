<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'user_id',
        'type',
        'message',
        'statut',
        'reponse',
        'date_reponse',
    ];

    protected $casts = [
        'date_reponse' => 'datetime',
    ];

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeAcceptee($query)
    {
        return $query->where('statut', 'acceptee');
    }

    public function scopeRefusee($query)
    {
        return $query->where('statut', 'refusee');
    }
}
