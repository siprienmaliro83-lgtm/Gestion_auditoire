<?php

namespace Database\Seeders;

use App\Models\Batiment;
use App\Models\Auditoire;
use Illuminate\Database\Seeder;

class InfrastructureSeeder extends Seeder
{
    public function run(): void
    {
        $batiments = [
            ['code' => 'B001', 'nom' => 'Batiment A', 'localisation' => 'Campus Principal', 'description' => 'Batiment principal - Sciences'],
            ['code' => 'B002', 'nom' => 'Batiment B', 'localisation' => 'Campus Principal', 'description' => 'Batiment secondaire - Sciences'],
            ['code' => 'B003', 'nom' => 'Batiment C', 'localisation' => 'Campus Nord', 'description' => 'Batiment - Économie et Gestion'],
            ['code' => 'B004', 'nom' => 'Batiment D', 'localisation' => 'Campus Nord', 'description' => 'Batiment - Amphithéâtres'],
            ['code' => 'B005', 'nom' => 'Batiment E', 'localisation' => 'Campus Sud', 'description' => 'Batiment - Laboratoires'],
        ];

        foreach ($batiments as $bData) {
            Batiment::create($bData);
        }

        $auditoires = [
            ['batiment_id' => 1, 'nom' => 'Amphi A1', 'capacite' => 300, 'etat' => 'Disponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A201', 'capacite' => 80, 'etat' => 'Disponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A202', 'capacite' => 60, 'etat' => 'Disponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A203', 'capacite' => 50, 'etat' => 'Disponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A301', 'capacite' => 40, 'etat' => 'Disponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A302', 'capacite' => 40, 'etat' => 'Maintenance'],
            ['batiment_id' => 2, 'nom' => 'Amphi B1', 'capacite' => 250, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B201', 'capacite' => 70, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B202', 'capacite' => 60, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B203', 'capacite' => 50, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B301', 'capacite' => 45, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B302', 'capacite' => 35, 'etat' => 'Indisponible'],
            ['batiment_id' => 3, 'nom' => 'Amphi C1', 'capacite' => 200, 'etat' => 'Disponible'],
            ['batiment_id' => 3, 'nom' => 'Salle C201', 'capacite' => 80, 'etat' => 'Disponible'],
            ['batiment_id' => 3, 'nom' => 'Salle C202', 'capacite' => 60, 'etat' => 'Disponible'],
            ['batiment_id' => 3, 'nom' => 'Salle C203', 'capacite' => 50, 'etat' => 'Disponible'],
            ['batiment_id' => 3, 'nom' => 'Salle C301', 'capacite' => 40, 'etat' => 'Disponible'],
            ['batiment_id' => 4, 'nom' => 'Amphi D1', 'capacite' => 400, 'etat' => 'Disponible'],
            ['batiment_id' => 4, 'nom' => 'Amphi D2', 'capacite' => 350, 'etat' => 'Disponible'],
            ['batiment_id' => 4, 'nom' => 'Salle D201', 'capacite' => 100, 'etat' => 'Disponible'],
            ['batiment_id' => 4, 'nom' => 'Salle D202', 'capacite' => 80, 'etat' => 'Disponible'],
            ['batiment_id' => 4, 'nom' => 'Salle D301', 'capacite' => 60, 'etat' => 'Maintenance'],
            ['batiment_id' => 5, 'nom' => 'Labo E101', 'capacite' => 30, 'etat' => 'Disponible'],
            ['batiment_id' => 5, 'nom' => 'Labo E102', 'capacite' => 30, 'etat' => 'Disponible'],
            ['batiment_id' => 5, 'nom' => 'Labo E201', 'capacite' => 25, 'etat' => 'Disponible'],
            ['batiment_id' => 5, 'nom' => 'Salle E301', 'capacite' => 50, 'etat' => 'Disponible'],
            ['batiment_id' => 5, 'nom' => 'Salle E302', 'capacite' => 40, 'etat' => 'Disponible'],
            ['batiment_id' => 5, 'nom' => 'Salle E303', 'capacite' => 35, 'etat' => 'Indisponible'],
            ['batiment_id' => 1, 'nom' => 'Salle A401', 'capacite' => 30, 'etat' => 'Disponible'],
            ['batiment_id' => 2, 'nom' => 'Salle B401', 'capacite' => 30, 'etat' => 'Disponible'],
        ];

        foreach ($auditoires as $aData) {
            Auditoire::create($aData);
        }
    }
}
