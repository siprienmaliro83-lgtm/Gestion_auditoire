<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrateur' => 'Paramétrage global, utilisateurs, attributions et statistiques.',
            'Décanat' => 'Préparation des horaires et demandes d\'auditoires pour son domaine.',
            'Enseignant' => 'Consultation des EC et programmations attribués.',
            'Étudiant' => 'Consultation des horaires et auditoires de sa promotion.',
        ];

        foreach ($roles as $nom => $description) {
            Role::updateOrCreate(['nom' => $nom], ['description' => $description]);
        }
    }
}
