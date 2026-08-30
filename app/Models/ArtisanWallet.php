<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtisanWallet extends Model
{
    protected $fillable = ['artisan_id', 'available_balance', 'reserved_balance'];

    protected $casts = [
        'available_balance' => 'integer',
        'reserved_balance' => 'integer',
    ];

    public function artisan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArtisanWalletEntry::class);
    }
}
