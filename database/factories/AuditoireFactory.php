<?php

namespace Database\Factories;

use App\Models\Batiment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoireFactory extends Factory
{
    public function definition(): array
    {
        return [
            'batiment_id' => Batiment::factory(),
            'nom' => 'Auditoire '.fake()->unique()->bothify('###'),
            'capacite' => fake()->numberBetween(40, 500),
            'etat' => fake()->randomElement(['Disponible', 'Disponible', 'Maintenance']),
        ];
    }
}
