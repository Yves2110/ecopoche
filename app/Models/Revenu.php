<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Revenu extends Model
{
    protected $fillable = [
        'budget_id', 'type', 'montant_brut',
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

    protected static function booted(): void
    {
        static::creating(function (Revenu $revenu) {
            if (in_array($revenu->type, ['bonus', 'extra'])) {
                $taux = 0.30;
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
