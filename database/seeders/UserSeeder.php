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
        $adminRoleId = Role::where('nom', 'Administrateur')->value('id');
        $decanatRoleId = Role::where('nom', 'Décanat')->value('id');
        $enseignantRoleId = Role::where('nom', 'Enseignant')->value('id');
        $etudiantRoleId = Role::where('nom', 'Étudiant')->value('id');

        $domaineST = Domaine::where('nom', 'Sciences et Technologies')->first();
        $domaineSEG = Domaine::where('nom', 'Sciences Économiques et de Gestion')->first();

        User::create([
            'role_id' => $adminRoleId,
            'name' => 'Administrateur',
            'email' => 'admin@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);

        User::create([
            'role_id' => $decanatRoleId,
            'name' => 'Decanat ST',
            'email' => 'decanat@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => true,
            'domaine_id' => $domaineST?->id,
        ]);

        User::create([
            'role_id' => $decanatRoleId,
            'name' => 'Decanat SEG',
            'email' => 'decanat.seg@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => true,
            'domaine_id' => $domaineSEG?->id,
        ]);

        $promotions = Promotion::all();

        $enseignants = [
            ['name' => 'Enseignant Jean', 'email' => 'enseignant@universite.cd'],
            ['name' => 'Enseignant Marie', 'email' => 'enseignant.marie@universite.cd'],
            ['name' => 'Enseignant Pierre', 'email' => 'enseignant.pierre@universite.cd'],
        ];

        foreach ($enseignants as $ens) {
            User::create([
                'role_id' => $enseignantRoleId,
                'name' => $ens['name'],
                'email' => $ens['email'],
                'password' => Hash::make('password'),
                'confirme' => true,
            ]);
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

            $etudiants[] = User::create([
                'role_id' => $etudiantRoleId,
                'name' => $prenom . ' ' . $noms[$i],
                'email' => strtolower($prenom) . '.' . strtolower($noms[$i]) . '@etudiant.universite.cd',
                'matricule' => sprintf('ETU-%04d', $i + 1),
                'password' => Hash::make('password'),
                'confirme' => true,
                'promotion_id' => $promoId,
            ]);
        }
    }
}
