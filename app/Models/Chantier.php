<?php

namespace App\Models;

use App\Enums\ChantierStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chantier extends Model
{
    use HasFactory;

    protected $table = 'artisan_chantiers';

    protected $fillable = [
        'artisan_id',
        'client_id',
        'nom',
        'type',
        'statut',
        'budget',
        'date_debut',
        'date_fin',
        'priorite',
        'materiel',
        'note_client',
        'checklist',
        'messages',
        'photos',
        'devis_lines',
        'acompte_paye',
        'solde_paye',
        'reception_validee',
    ];

    protected function casts(): array
    {
        return [
            'statut' => ChantierStatus::class,
            'budget' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'checklist' => 'array',
            'messages' => 'array',
            'photos' => 'array',
            'devis_lines' => 'array',
            'acompte_paye' => 'boolean',
            'solde_paye' => 'boolean',
            'reception_validee' => 'boolean',
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(MembreEquipe::class, 'chantier_membre')
            ->withPivot('role_sur_chantier')
            ->withTimestamps();
    }

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function transactions()
    {
        return $this->hasMany(ChantierTransaction::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Calculate progress percentage based on status.
     */
    public function getProgressAttribute(): int
    {
        return match ($this->statut) {
            ChantierStatus::DEVIS => 10,
            ChantierStatus::EN_COURS => 40,
            ChantierStatus::ATTENTE => 60,
            ChantierStatus::TERMINE => 100,
            ChantierStatus::ARRET => 50,
        };
    }

    /**
     * Check if all checklist items are done.
     */
    public function getChecklistCompletedAttribute(): bool
    {
        $checklist = $this->checklist ?? [];
        if (empty($checklist)) {
            return false;
        }

        return collect($checklist)->every(fn ($item) => is_array($item) && ($item['done'] ?? false));
    }

    /**
     * Calculate profitability: CA (paid invoices) - expenses.
     */
    public function calculerRentabilite(): float
    {
        $ca = $this->factures()
            ->where('statut', 'payee')
            ->sum('montant_ttc');

        $depenses = $this->depenses()->sum('montant');

        return round($ca - $depenses, 2);
    }

    /**
     * Get total revenue from paid invoices.
     */
    public function getTotalCAAttribute(): float
    {
        return round($this->factures()
            ->where('statut', 'payee')
            ->sum('montant_ttc'), 2);
    }

    /**
     * Get total expenses.
     */
    public function getTotalDepensesAttribute(): float
    {
        return round($this->depenses()->sum('montant'), 2);
    }
}
