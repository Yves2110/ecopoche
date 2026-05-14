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
        'role', 'is_active', 'created_by', 'devise',
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
