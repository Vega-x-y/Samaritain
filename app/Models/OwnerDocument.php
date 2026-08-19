<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class OwnerDocument extends Model
{
    use HasFactory;

    protected $table = 'owner_documents';

    protected $fillable = [
        'property_id',
        'name',
        'category',
        'file_path',
        'file_size',
        'documentable_id',
        'documentable_type',
        'created_by',
    ];

    public const CATEGORIES = [
        'invoice' => 'Facture',
        'receipt' => 'Reçu',
        'quote' => 'Devis',
        'inspection' => 'État des lieux',
        'other' => 'Autre',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getMimeTypeAttribute(): string
    {
        return Storage::mimeType($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
