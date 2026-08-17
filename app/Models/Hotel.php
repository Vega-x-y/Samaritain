<?php

namespace App\Models;

use App\Enums\HotelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'slug',
        'description',
        'price_per_night',
        'price_per_hour',
        'star_rating',
        'rooms',
        'bathrooms',
        'furnished',
        'address',
        'contact',
        'city_id',
        'arrondissement_id',
        'status',
        'is_verify',
        'is_active',
        'views',
        'conditions_accepted_at',
        'hourly_prices',
    ];

    protected $casts = [
        'status' => HotelStatus::class,
        'conditions_accepted_at' => 'datetime',
        'hourly_prices' => 'array',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Hotel $hotel) {
            if (empty($hotel->slug)) {
                $hotel->slug = static::generateUniqueSlug($hotel->title);
            }
        });

        static::updating(function (Hotel $hotel) {
            if ($hotel->isDirty('title') && ! $hotel->isDirty('slug')) {
                $hotel->slug = static::generateUniqueSlug($hotel->title, $hotel->id);
            }
        });
    }

    /**
     * Generate a unique slug for a hotel.
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        $query = static::withTrashed()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
            $query = static::withTrashed()->where('slug', $slug);
            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function arrondissement(): BelongsTo
    {
        return $this->belongsTo(Arrondissement::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_hotel');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HotelImage::class);
    }

    public function getCoverImageUrlAttribute()
    {
        $coverImage = $this->images()->where('cover_image', true)->first();

        return $coverImage ? $coverImage->image_url : null;
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function getPriceLabelAttribute(): string
    {
        return '/heure';
    }
}
