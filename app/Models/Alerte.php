<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerte extends Model
{
    protected $fillable = [
        'user_id', 'type', 'gravite', 'message', 'meta', 'lu_at',
    ];

    protected $casts = [
        'meta'  => 'array',
        'lu_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isLue(): bool
    {
        return $this->lu_at !== null;
    }

    public function marquerLue(): void
    {
        $this->update(['lu_at' => now()]);
    }
}
