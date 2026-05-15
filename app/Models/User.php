<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role', 'is_active', 'created_by', 'devise', 'quota_taux', 'notifs_email',
        'seuil_attention', 'seuil_critique', 'seuil_plafond_cat',
        'objectif_epargne_pct', 'jour_bilan_email', 'mode_discret', 'epargne_salaire_pct',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'quota_taux'           => 'integer',
            'notifs_email'         => 'boolean',
            'seuil_attention'      => 'integer',
            'seuil_critique'       => 'integer',
            'seuil_plafond_cat'    => 'integer',
            'objectif_epargne_pct' => 'integer',
            'jour_bilan_email'     => 'integer',
            'mode_discret'         => 'boolean',
            'epargne_salaire_pct'  => 'integer',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Categorie::class);
    }

    public function alertes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function budgetDuMois(int $mois, int $annee): ?Budget
    {
        return $this->budgets()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->first();
    }
}
