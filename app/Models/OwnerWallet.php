<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnerWallet extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'available_balance', 'reserved_balance'];

    protected $casts = ['available_balance' => 'integer', 'reserved_balance' => 'integer'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(WalletEntry::class);
    }
}
