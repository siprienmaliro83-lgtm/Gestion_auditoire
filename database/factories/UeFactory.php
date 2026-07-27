<?php

namespace Database\Factories;

use App\Models\ProgrammeAcademique;
use Illuminate\Database\Eloquent\Factories\Factory;

class UeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programme_academique_id' => ProgrammeAcademique::factory(),
            'code' => strtoupper(fake()->unique()->bothify('UE-###')),
            'nom' => fake()->unique()->words(3, true),
            'credits' => fake()->numberBetween(2, 8),
            'volume_horaire' => fake()->randomElement([30, 45, 60, 75, 90]),
        ];
    }
}
