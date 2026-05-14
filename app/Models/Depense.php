<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = [
        'budget_id', 'categorie_id', 'montant',
        'date', 'note', 'imprevue',
    ];

    protected $casts = [
        'montant'  => 'decimal:2',
        'date'     => 'date',
        'imprevue' => 'boolean',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}
