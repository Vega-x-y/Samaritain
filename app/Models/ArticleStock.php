<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleStock extends Model
{
    use HasFactory;

    protected $table = 'articles_stock';

    protected $fillable = [
        'artisan_id',
        'nom',
        'reference',
        'categorie',
        'quantite',
        'seuil_alerte',
        'prix_unitaire',
        'fournisseur',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'seuil_alerte' => 'integer',
            'prix_unitaire' => 'decimal:2',
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'article_id');
    }

    public function getStockAlerteAttribute(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
    }

    public function getValeurTotaleAttribute(): float
    {
        return $this->quantite * ($this->prix_unitaire ?? 0);
    }
}
