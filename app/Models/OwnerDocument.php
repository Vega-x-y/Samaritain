<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
}