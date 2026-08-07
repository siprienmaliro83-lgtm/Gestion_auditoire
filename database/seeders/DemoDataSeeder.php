<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Données de démonstration pour le développement local.
 *
 * Usage : php artisan db:seed --class=DemoDataSeeder
 *
 * Contrairement à `DatabaseSeeder` (initialisation minimale de production),
 * ce seeder peuple la base avec la structure académique complète, les
 * infrastructures et des utilisateurs fictifs.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            AcademicStructureSeeder::class,
            InfrastructureSeeder::class,
            UserSeeder::class,
        ]);
    }
}
