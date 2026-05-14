<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nom', 'icone', 'couleur',
        'type', 'plafond_mensuel', 'is_default', 'is_active', 'ordre',
    ];

    protected $casts = [
        'plafond_mensuel' => 'decimal:2',
        'is_default'      => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'categorie_id');
    }
}
