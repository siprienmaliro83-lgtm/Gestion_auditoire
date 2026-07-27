<?php

namespace Database\Factories;

use App\Models\Domaine;
use Illuminate\Database\Eloquent\Factories\Factory;

class FiliereFactory extends Factory
{
    public function definition(): array
    {
        return [
            'domaine_id' => Domaine::factory(),
            'code' => strtoupper(fake()->unique()->bothify('FIL-###')),
            'nom' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
