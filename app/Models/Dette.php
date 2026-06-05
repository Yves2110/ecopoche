<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dette extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'partie', 'montant_initial',
        'date_operation', 'date_echeance', 'interet_pct',
        'affecte_budget', 'statut', 'note',
    ];

    protected $casts = [
        'montant_initial' => 'decimal:2',
        'interet_pct'     => 'decimal:2',
        'affecte_budget'  => 'boolean',
        'date_operation'  => 'date',
        'date_echeance'   => 'date',
    ];

    protected $appends = ['montant_rembourse', 'montant_restant', 'pct_rembourse', 'est_en_retard'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remboursements(): HasMany
    {
        return $this->hasMany(Remboursement::class)->orderByDesc('date');
    }

    // ── Attributs calculés ─────────────────────────────────────────────────

    public function getMontantRembourseAttribute(): float
    {
        return (float) $this->remboursements()->sum('montant');
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, (float) $this->montant_initial - $this->montant_rembourse);
    }

    public function getPctRembourseAttribute(): float
    {
        if ($this->montant_initial <= 0) return 0;
        return min(100, round($this->montant_rembourse / $this->montant_initial * 100, 1));
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->statut !== 'solde'
            && $this->date_echeance
            && $this->date_echeance->isPast();
    }

    /**
     * Recalcule et persiste le statut selon les remboursements et l'échéance.
     */
    public function recalculerStatut(): void
    {
        $this->statut = match (true) {
            $this->montant_restant <= 0.01            => 'solde',
            $this->est_en_retard                       => 'en_retard',
            default                                    => 'actif',
        };
        $this->save();
    }
}
