<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->randomElement(['Administrateur', 'Décanat', 'Enseignant', 'Étudiant']),
            'description' => fake()->sentence(),
        ];
    }
}
