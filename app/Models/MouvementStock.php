<?php

namespace App\Models;

use App\Enums\MouvementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    use HasFactory;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'article_id',
        'type',
        'quantite',
        'motif',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'type' => MouvementType::class,
            'date' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(ArticleStock::class);
    }
}
