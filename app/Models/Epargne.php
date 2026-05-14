<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Epargne extends Model
{
    protected $fillable = [
        'budget_id', 'objectif', 'reel', 'deficit', 'analyse',
    ];

    protected $casts = [
        'objectif' => 'decimal:2',
        'reel'     => 'decimal:2',
        'deficit'  => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function isEnDeficit(): bool
    {
        return $this->reel < $this->objectif;
    }

    public function getPourcentageAttribute(): float
    {
        if ($this->objectif <= 0) return 0;
        return min(100, round(($this->reel / $this->objectif) * 100, 1));
    }
}
