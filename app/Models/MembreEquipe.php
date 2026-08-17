<?php

namespace App\Models;

use App\Enums\MembreStatut;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MembreEquipe extends Model
{
    use HasFactory;

    protected $table = 'membre_equipe';

    protected $fillable = [
        'artisan_id',
        'user_id',
        'nom',
        'role',
        'telephone',
        'email',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'statut' => MembreStatut::class,
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

    public function chantiers(): BelongsToMany
    {
        return $this->belongsToMany(Chantier::class, 'chantier_membre')
            ->withPivot('role_sur_chantier')
            ->withTimestamps();
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
