<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        // Initialisation minimale et idempotente d'une base de production :
        // rôles par défaut + Super Administrateur. Les données de démonstration
        // (domaines, filières, mentions, enseignants, étudiants...) sont créées
        // via `DemoDataSeeder` ou directement dans l'application.
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
