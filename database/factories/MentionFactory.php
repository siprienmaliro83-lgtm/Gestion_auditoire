<?php

namespace Database\Factories;

use App\Models\Filiere;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'filiere_id' => Filiere::factory(),
            'code' => strtoupper(fake()->unique()->bothify('MEN-###')),
            'nom' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
