<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnneeAcademiqueFactory extends Factory
{
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2024, 2035);

        return [
            'libelle' => $year.'-'.($year + 1),
            'date_debut' => "$year-09-01",
            'date_fin' => ($year + 1).'-08-31',
            'active' => false,
        ];
    }
}
