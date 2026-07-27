<?php

namespace Database\Factories;

use App\Models\Auditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgrammationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'demande_auditoire_id' => null,
            'ec_id' => Ec::factory(),
            'enseignant_id' => Enseignant::factory(),
            'auditoire_id' => Auditoire::factory(),
            'promotions_concernees' => [],
            'date_debut' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'date_fin' => fake()->dateTimeBetween('+2 months', '+4 months')->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'effectif_total' => fake()->numberBetween(40, 250),
            'statut' => 'Validée',
            'validee_par' => null,
            'validee_a' => now(),
        ];
    }
}
