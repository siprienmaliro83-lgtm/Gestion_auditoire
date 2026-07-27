<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnseignantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'matricule' => strtoupper(fake()->unique()->bothify('ENS-####')),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'grade' => fake()->randomElement(['Assistant', 'Chef de travaux', 'Professeur']),
        ];
    }
}
