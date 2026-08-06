<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    public const TYPE_DEVIS = 'devis';

    public const TYPE_FACTURE = 'facture';

    public const TYPE_ATTESTATION = 'attestation';

    public const TYPE_COMPTE_RENDU = 'compte_rendu';

    public const TYPE_IMAGE = 'image';

    public const TYPE_PDF = 'pdf';

    public const TYPE_DOCUMENT = 'document';

    public const TYPES = [
        self::TYPE_DEVIS => 'Devis',
        self::TYPE_FACTURE => 'Facture',
        self::TYPE_ATTESTATION => 'Attestation',
        self::TYPE_COMPTE_RENDU => 'Compte rendu',
        self::TYPE_IMAGE => 'Image',
        self::TYPE_PDF => 'PDF',
        self::TYPE_DOCUMENT => 'Document',
    ];

    public const ICONS = [
        self::TYPE_DEVIS => 'calculator',
        self::TYPE_FACTURE => 'receipt',
        self::TYPE_ATTESTATION => 'stamp',
        self::TYPE_COMPTE_RENDU => 'clipboard-list',
        self::TYPE_IMAGE => 'image',
        self::TYPE_PDF => 'file-text',
        self::TYPE_DOCUMENT => 'file',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_SIGNED = 'signed';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_SENT => 'Envoyé',
        self::STATUS_SIGNED => 'Signé',
    ];

    protected $fillable = [
        'chantier_id',
        'client_id',
        'property_id',
        'name',
        'category',
        'path',
        'file_path',
        'file_size',
        'type',
        'mime_type',
        'size',
        'date_modification',
        'metadata',
        'status',
        'signed_at',
        'signature_data',
        'documentable_id',
        'documentable_type',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'file_size' => 'integer',
            'date_modification' => 'datetime',
            'metadata' => 'array',
            'signed_at' => 'datetime',
            'signature_data' => 'array',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

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
        return Storage::disk('r2')->url($this->path ?? $this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?? $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getTypeIconAttribute(): string
    {
        return self::ICONS[$this->type] ?? 'file';
    }

    public function getFormattedDateModificationAttribute(): ?string
    {
        if (! $this->date_modification) {
            return null;
        }

        return $this->date_modification->format('d/m/Y H:i');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isDevis(): bool
    {
        return $this->type === self::TYPE_DEVIS;
    }

    public function canExport(): bool
    {
        if (! $this->isDevis()) {
            return true;
        }

        return $this->isSigned();
    }

    public function scopeByType($query, ?string $type)
    {
        if ($type) {
            return $query->where('type', $type);
        }

        return $query;
    }

    public function scopeByClient($query, ?int $clientId)
    {
        if ($clientId) {
            return $query->where('client_id', $clientId);
        }

        return $query;
    }

    public function scopeDevis($query)
    {
        return $query->where('type', self::TYPE_DEVIS);
    }
}