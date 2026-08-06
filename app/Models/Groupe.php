<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'nom',
        'description',
    ];

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MessageGroupe::class);
    }

    public function getDernierMessageAttribute(): ?MessageGroupe
    {
        return $this->messages()->latest()->first();
    }
}
