<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectifEpargne extends Model
{
    protected $table = 'objectifs_epargne';

    protected $fillable = [
        'user_id', 'nom', 'montant_cible', 'montant_actuel',
        'date_debut', 'date_fin', 'couleur', 'icone', 'note', 'atteint',
    ];

    protected $casts = [
        'date_debut'     => 'date',
        'date_fin'       => 'date',
        'montant_cible'  => 'decimal:2',
        'montant_actuel' => 'decimal:2',
        'atteint'        => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPourcentageAttribute(): int
    {
        if ($this->montant_cible <= 0) return 0;
        return (int) min(100, round($this->montant_actuel / $this->montant_cible * 100));
    }

    public function getRestantAttribute(): int
    {
        return (int) max(0, $this->montant_cible - $this->montant_actuel);
    }

    public function getMoisRestantsAttribute(): ?int
    {
        if (!$this->date_fin) return null;
        return (int) max(0, now()->startOfMonth()->diffInMonths($this->date_fin->startOfMonth()));
    }
}
