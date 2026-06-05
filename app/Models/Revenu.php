<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Revenu extends Model
{
    protected $fillable = [
        'budget_id', 'dette_id', 'remboursement_id',
        'type', 'montant_brut',
        'montant_quota', 'montant_dispo',
        'date', 'description', 'quota_applique',
    ];

    protected $casts = [
        'montant_brut'    => 'decimal:2',
        'montant_quota'   => 'decimal:2',
        'montant_dispo'   => 'decimal:2',
        'quota_applique'  => 'boolean',
        'date'            => 'date',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function quotaLog(): HasOne
    {
        return $this->hasOne(QuotaLog::class);
    }

    /**
     * Part utilisable immédiatement (solde dépensable, dépenses).
     * - Avec quota 30/70 : montant_quota
     * - Sans quota (ex. prêt/emprunt lié) : 100 % dans montant_dispo
     */
    public function montantDepensable(): float
    {
        return $this->quota_applique
            ? (float) $this->montant_quota
            : (float) $this->montant_dispo;
    }

    /** Part en réserve bonus (70 %), 0 si pas de quota. */
    public function montantReserve(): float
    {
        return $this->quota_applique ? (float) $this->montant_dispo : 0.0;
    }

    /** @param  iterable<Revenu>  $revenus */
    public static function sumDepensable(iterable $revenus): float
    {
        $total = 0.0;
        foreach ($revenus as $revenu) {
            $total += $revenu->montantDepensable();
        }

        return $total;
    }

    /** @param  iterable<Revenu>  $revenus */
    public static function sumReserve(iterable $revenus): float
    {
        $total = 0.0;
        foreach ($revenus as $revenu) {
            $total += $revenu->montantReserve();
        }

        return $total;
    }

    protected static function booted(): void
    {
        static::creating(function (Revenu $revenu) {
            if ($revenu->dette_id !== null || $revenu->remboursement_id !== null) {
                return;
            }

            if (in_array($revenu->type, ['bonus', 'extra'])) {
                $tauxPct = (int) (auth()->user()?->quota_taux ?? 30);
                $taux = $tauxPct / 100;
                $revenu->montant_quota  = round($revenu->montant_brut * $taux, 2);
                $revenu->montant_dispo  = round($revenu->montant_brut * (1 - $taux), 2);
                $revenu->quota_applique = true;
            } else {
                $revenu->montant_quota  = 0;
                $revenu->montant_dispo  = $revenu->montant_brut;
                $revenu->quota_applique = false;
            }
        });
    }
}
