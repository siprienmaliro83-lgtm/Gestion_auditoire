<?php

namespace Database\Factories;

use App\Models\AnneeAcademique;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammeAcademiqueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'annee_academique_id' => AnneeAcademique::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PRG-###')),
            'nom' => fake()->unique()->words(4, true),
            'description' => fake()->sentence(),
        ];
    }
}
