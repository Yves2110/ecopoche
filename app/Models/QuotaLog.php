<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotaLog extends Model
{
    protected $fillable = [
        'revenu_id', 'montant_brut', 'montant_quota',
        'montant_dispo', 'taux', 'debloquer', 'justification_deblocage',
    ];

    protected $casts = [
        'montant_brut'  => 'decimal:2',
        'montant_quota' => 'decimal:2',
        'montant_dispo' => 'decimal:2',
        'taux'          => 'decimal:2',
        'debloquer'     => 'decimal:2',
    ];

    public function revenu(): BelongsTo
    {
        return $this->belongsTo(Revenu::class);
    }

    public function getReserveRestanteAttribute(): float
    {
        return (float) $this->montant_quota - (float) $this->debloquer;
    }
}
