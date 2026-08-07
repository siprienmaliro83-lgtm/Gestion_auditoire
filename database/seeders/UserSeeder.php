<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Domaine;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $decanatRoleId = Role::where('nom', 'Décanat')->value('id');
        $enseignantRoleId = Role::where('nom', 'Enseignant')->value('id');
        $etudiantRoleId = Role::where('nom', 'Étudiant')->value('id');

        $domaineST = Domaine::where('nom', 'Sciences et Technologies')->first();
        $domaineSEG = Domaine::where('nom', 'Sciences Économiques et de Gestion')->first();

        // Le compte administrateur (admin@universite.cd) est créé par
        // `SuperAdminSeeder` : on ne le recrée pas ici pour éviter tout doublon.
        $decanatUsers = [
            [
                'role_id' => $decanatRoleId,
                'name' => 'Decanat ST',
                'email' => 'decanat@universite.cd',
                'domaine_id' => $domaineST?->id,
            ],
            [
                'role_id' => $decanatRoleId,
                'name' => 'Decanat SEG',
                'email' => 'decanat.seg@universite.cd',
                'domaine_id' => $domaineSEG?->id,
            ],
        ];

        foreach ($decanatUsers as $decanatUser) {
            User::updateOrCreate(
                ['email' => $decanatUser['email']],
                [
                    'role_id' => $decanatUser['role_id'],
                    'name' => $decanatUser['name'],
                    'password' => Hash::make('password'),
                    'confirme' => true,
                    'domaine_id' => $decanatUser['domaine_id'],
                ],
            );
        }

        $promotions = Promotion::all();

        $enseignants = [
            ['name' => 'Enseignant Jean', 'email' => 'enseignant@universite.cd'],
            ['name' => 'Enseignant Marie', 'email' => 'enseignant.marie@universite.cd'],
            ['name' => 'Enseignant Pierre', 'email' => 'enseignant.pierre@universite.cd'],
        ];

        foreach ($enseignants as $ens) {
            User::updateOrCreate(
                ['email' => $ens['email']],
                [
                    'role_id' => $enseignantRoleId,
                    'name' => $ens['name'],
                    'password' => Hash::make('password'),
                    'confirme' => true,
                ],
            );
        }

        $etudiants = [];
        $promoIds = $promotions->pluck('id')->toArray();

        $noms = [
            'Kasongo', 'Mwamba', 'Tshilumba', 'Lukusa', 'Ngandu', 'Kabongo', 'Mutombo',
            'Ilunga', 'Kalala', 'Mbuyi', 'Tshimanga', 'Wekesa', 'Nkusu', 'Bakenga',
            'Mukalay', 'Kasereka', 'Lubambo', 'Balume', 'Musubao', 'Kintu',
        ];

        for ($i = 0; $i < 20; $i++) {
            $promoId = $promoIds[$i % count($promoIds)];
            $prenom = ['Alice', 'Bob', 'Claire', 'David', 'Emma', 'Franck', 'Grace', 'Hugo',
                        'Isabelle', 'Jules', 'Karine', 'Louis', 'Monique', 'Nathan', 'Olivia',
                        'Patrick', 'Quentin', 'Rachel', 'Samuel', 'Thérèse'][$i];

            $email = strtolower($prenom) . '.' . strtolower($noms[$i]) . '@etudiant.universite.cd';

            $etudiants[] = User::updateOrCreate(
                ['email' => $email],
                [
                    'role_id' => $etudiantRoleId,
                    'name' => $prenom . ' ' . $noms[$i],
                    'matricule' => sprintf('ETU-%04d', $i + 1),
                    'password' => Hash::make('password'),
                    'confirme' => true,
                    'promotion_id' => $promoId,
                ],
            );
        }
    }
}
