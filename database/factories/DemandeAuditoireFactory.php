<?php

namespace Database\Factories;

use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeAuditoireFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ec_id' => Ec::factory(),
            'enseignant_id' => Enseignant::factory(),
            'promotions_concernees' => [],
            'date_debut' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'date_fin' => fake()->dateTimeBetween('+2 months', '+4 months')->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'effectif_total' => fake()->numberBetween(40, 250),
            'statut' => 'En attente',
            'motif_refus' => null,
            'envoyee_a' => now(),
        ];
    }
}
