<?php

namespace Database\Factories;

use App\Models\Mention;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mention_id' => Mention::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PRO-###')),
            'nom' => fake()->unique()->randomElement(['L1', 'L2', 'L3', 'M1', 'M2']).' '.fake()->word(),
            'niveau' => fake()->numberBetween(1, 5),
            'effectif' => fake()->numberBetween(25, 180),
        ];
    }
}
