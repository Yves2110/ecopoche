<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'mois', 'annee',
        'salaire_fixe', 'solde_charges', 'epargne_objectif', 'archive',
    ];

    protected $casts = [
        'salaire_fixe'    => 'decimal:2',
        'solde_charges'   => 'decimal:2',
        'epargne_objectif'=> 'decimal:2',
        'archive'         => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revenus(): HasMany
    {
        return $this->hasMany(Revenu::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }

    public function epargne(): HasOne
    {
        return $this->hasOne(Epargne::class);
    }

    public function getTotalRevenusAttribute(): float
    {
        return (float) $this->revenus()->sum('montant_dispo') + (float) $this->salaire_fixe;
    }

    public function getTotalDepensesAttribute(): float
    {
        return (float) $this->depenses()->sum('montant');
    }

    public function getSoldeDisponibleAttribute(): float
    {
        return $this->total_revenus - $this->total_depenses;
    }

    public function getSoldeReserveAttribute(): float
    {
        return (float) $this->revenus()
            ->where('quota_applique', true)
            ->sum('montant_quota');
    }
}
