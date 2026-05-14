<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nom'     => $this->faker->word(),
            'couleur' => $this->faker->hexColor(),
            'plafond_mensuel' => $this->faker->numberBetween(10000, 200000),
        ];
    }
}
