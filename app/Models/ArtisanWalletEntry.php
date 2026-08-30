<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtisanWalletEntry extends Model
{
    protected $fillable = ['artisan_wallet_id', 'transaction_id', 'kind', 'amount', 'metadata'];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ArtisanWallet::class, 'artisan_wallet_id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }
}
