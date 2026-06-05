<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recurrence extends Model
{
    protected $fillable = [
        'user_id', 'type', 'categorie_id', 'revenu_type',
        'montant', 'jour_du_mois', 'libelle', 'imprevue',
        'is_active', 'last_generated_ym',
    ];

    protected $casts = [
        'montant'      => 'integer',
        'jour_du_mois' => 'integer',
        'imprevue'     => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function isDepense(): bool
    {
        return $this->type === 'depense';
    }

    public function isRevenu(): bool
    {
        return $this->type === 'revenu';
    }
}
