<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bureau extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'price_type',
        'surface',
        'rooms',
        'address',
        'location',
        'phone',
        'email',
        'active',
        'latitude',
        'longitude',
        'city_id',
        'arrondissement_id',
        'category_id',
        'created_by',
        'is_active',
        'is_verify',
        'conditions_accepted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verify' => 'boolean',
        'price' => 'integer',
        'surface' => 'integer',
        'rooms' => 'integer',
        'conditions_accepted_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Bureau $bureau) {
            if (empty($bureau->slug)) {
                $bureau->slug = static::generateUniqueSlug($bureau->name);
            }
        });

        static::updating(function (Bureau $bureau) {
            if ($bureau->isDirty('name') && ! $bureau->isDirty('slug')) {
                $bureau->slug = static::generateUniqueSlug($bureau->name, $bureau->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        $query = static::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
            $query = static::where('slug', $slug);
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(BureauImage::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_bureau');
    }

    public function getCoverImageUrlAttribute(): string
    {
        $cover = $this->images->where('cover_image', true)->first();
        if ($cover) {
            return $cover->image_url;
        }

        $first = $this->images->first();
        if ($first) {
            return $first->image_url;
        }

        return asset('images/placeholder-property.jpg');
    }
}
