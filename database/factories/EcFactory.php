<?php

namespace Database\Factories;

use App\Models\Ue;
use Illuminate\Database\Eloquent\Factories\Factory;

class EcFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ue_id' => Ue::factory(),
            'code' => strtoupper(fake()->unique()->bothify('EC-###')),
            'nom' => fake()->unique()->words(3, true),
            'volume_horaire' => fake()->randomElement([15, 30, 45, 60]),
            'statut' => fake()->randomElement(['Non commencé', 'En cours']),
        ];
    }
}
