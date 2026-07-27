<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DomaineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('DOM-##')),
            'nom' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
