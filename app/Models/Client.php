<?php

namespace App\Models;

use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'user_id',
        'nom',
        'telephone',
        'email',
        'adresse',
        'type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ClientType::class,
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chantiers(): HasMany
    {
        return $this->hasMany(Chantier::class);
    }

    public function getInitialAttribute(): string
    {
        $parts = explode(' ', trim($this->nom));

        return collect($parts)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
    }
}
