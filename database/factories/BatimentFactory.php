<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BatimentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('BAT-##')),
            'nom' => 'Bâtiment '.fake()->unique()->bothify('??'),
            'localisation' => fake()->streetAddress(),
            'description' => fake()->sentence(),
        ];
    }
}
