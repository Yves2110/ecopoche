<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'mois'             => now()->month,
            'annee'            => now()->year,
            'salaire_fixe'     => 0,
            'solde_charges'    => 0,
            'epargne_objectif' => 0,
            'archive'          => false,
        ];
    }
}
