<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObjectifEpargneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'nom'            => $this->faker->words(2, true),
            'montant_cible'  => $this->faker->numberBetween(50000, 1000000),
            'montant_actuel' => 0,
            'date_debut'     => now()->format('Y-m-d'),
            'date_fin'       => null,
            'couleur'        => '#006c49',
            'icone'          => 'savings',
            'note'           => null,
            'atteint'        => false,
        ];
    }
}
