<?php

namespace App\Models;

use App\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'price',
        'surface',
        'rooms',
        'bedrooms',
        'floor',
        'furnished',
        'address',
        'status',
        'verified'
    ];

    protected $casts = [
        'status' => PropertyStatus::class,
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
