<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HotelImage extends Model
{
    protected $fillable = ['image_url', 'cover_image', 'hotel_id'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function getImageUrlAttribute(string $value): string
    {
        // Si l'URL est déjà absolue (http/https), on la retourne telle quelle
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::url($value);
    }
}
