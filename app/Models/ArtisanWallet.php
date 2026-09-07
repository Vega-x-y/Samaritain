<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArtisanWallet extends Model
{
    protected $fillable = ['artisan_id', 'available_balance', 'reserved_balance'];

    protected $casts = [
        'available_balance' => 'integer',
        'reserved_balance' => 'integer',
    ];

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ArtisanWalletEntry::class);
    }
}
