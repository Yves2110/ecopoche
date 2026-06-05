<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Remboursement extends Model
{
    protected $fillable = [
        'dette_id', 'montant', 'date', 'affecte_budget', 'note',
    ];

    protected $casts = [
        'montant'        => 'decimal:2',
        'date'           => 'date',
        'affecte_budget' => 'boolean',
    ];

    public function dette(): BelongsTo
    {
        return $this->belongsTo(Dette::class);
    }
}
