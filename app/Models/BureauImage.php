<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BureauImage extends Model
{
    protected $fillable = [
        'bureau_id',
        'image_url',
        'cover_image',
    ];

    protected $casts = [
        'cover_image' => 'boolean',
    ];

    public function bureau(): BelongsTo
    {
        return $this->belongsTo(Bureau::class);
    }

    public function getImageUrlAttribute(string $value): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::url($value);
    }
}

