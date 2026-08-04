<?php

namespace Database\Seeders;

use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\AnneeAcademique;
use App\Models\ProgrammeAcademique;
use App\Models\Ue;
use App\Models\Ec;
use App\Models\Enseignant;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeAcademique::create([
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-06-30',
            'active' => true,
        ]);

        $annee2 = AnneeAcademique::create([
            'libelle' => '2026-2027',
            'date_debut' => '2026-09-01',
            'date_fin' => '2027-06-30',
            'active' => false,
        ]);

        $domaines = [
            ['code' => 'D001', 'nom' => 'Sciences et Technologies', 'filieres' => [
                ['code' => 'F001', 'nom' => 'Informatique', 'mentions' => [
                    ['code' => 'M001', 'nom' => 'Génie Logiciel', 'promotions' => [
                        ['code' => 'P001', 'nom' => 'L1 GL', 'niveau' => 1, 'effectif' => 120],
                        ['code' => 'P002', 'nom' => 'L2 GL', 'niveau' => 2, 'effectif' => 100],
                        ['code' => 'P003', 'nom' => 'L3 GL', 'niveau' => 3, 'effectif' => 80],
                    ]],
                    ['code' => 'M002', 'nom' => 'Réseaux et Systèmes', 'promotions' => [
                        ['code' => 'P004', 'nom' => 'L1 RS', 'niveau' => 1, 'effectif' => 110],
                        ['code' => 'P005', 'nom' => 'L2 RS', 'niveau' => 2, 'effectif' => 90],
                        ['code' => 'P006', 'nom' => 'L3 RS', 'niveau' => 3, 'effectif' => 70],
                    ]],
                ]],
                ['code' => 'F002', 'nom' => 'Mathématiques', 'mentions' => [
                    ['code' => 'M003', 'nom' => 'Mathématiques Pures', 'promotions' => [
                        ['code' => 'P007', 'nom' => 'L1 MP', 'niveau' => 1, 'effectif' => 80],
                        ['code' => 'P008', 'nom' => 'L2 MP', 'niveau' => 2, 'effectif' => 65],
                        ['code' => 'P009', 'nom' => 'L3 MP', 'niveau' => 3, 'effectif' => 50],
                    ]],
                    ['code' => 'M004', 'nom' => 'Statistique', 'promotions' => [
                        ['code' => 'P010', 'nom' => 'L1 ST', 'niveau' => 1, 'effectif' => 90],
                        ['code' => 'P011', 'nom' => 'L2 ST', 'niveau' => 2, 'effectif' => 75],
                        ['code' => 'P012', 'nom' => 'L3 ST', 'niveau' => 3, 'effectif' => 60],
                    ]],
                ]],
            ]],
            ['code' => 'D002', 'nom' => 'Sciences Économiques et de Gestion', 'filieres' => [
                ['code' => 'F003', 'nom' => 'Gestion', 'mentions' => [
                    ['code' => 'M005', 'nom' => 'Gestion des Entreprises', 'promotions' => [
                        ['code' => 'P013', 'nom' => 'L1 GE', 'niveau' => 1, 'effectif' => 150],
                        ['code' => 'P014', 'nom' => 'L2 GE', 'niveau' => 2, 'effectif' => 130],
                        ['code' => 'P015', 'nom' => 'L3 GE', 'niveau' => 3, 'effectif' => 110],
                    ]],
                    ['code' => 'M006', 'nom' => 'Comptabilité', 'promotions' => [
                        ['code' => 'P016', 'nom' => 'L1 CO', 'niveau' => 1, 'effectif' => 140],
                    ]],
                ]],
                ['code' => 'F004', 'nom' => 'Économie', 'mentions' => [
                    ['code' => 'M007', 'nom' => 'Économie Générale', 'promotions' => [
                        ['code' => 'P017', 'nom' => 'L1 EG', 'niveau' => 1, 'effectif' => 100],
                        ['code' => 'P018', 'nom' => 'L2 EG', 'niveau' => 2, 'effectif' => 85],
                        ['code' => 'P019', 'nom' => 'L3 EG', 'niveau' => 3, 'effectif' => 70],
                    ]],
                    ['code' => 'M008', 'nom' => 'Finance', 'promotions' => [
                        ['code' => 'P020', 'nom' => 'L1 FI', 'niveau' => 1, 'effectif' => 95],
                    ]],
                ]],
            ]],
        ];

        foreach ($domaines as $dData) {
            $domaine = Domaine::create([
                'code' => $dData['code'],
                'nom' => $dData['nom'],
            ]);

            foreach ($dData['filieres'] as $fData) {
                $filiere = Filiere::create([
                    'domaine_id' => $domaine->id,
                    'code' => $fData['code'],
                    'nom' => $fData['nom'],
                ]);

                foreach ($fData['mentions'] as $mData) {
                    $mention = Mention::create([
                        'filiere_id' => $filiere->id,
                        'code' => $mData['code'],
                        'nom' => $mData['nom'],
                    ]);

                    foreach ($mData['promotions'] as $pData) {
                        Promotion::create([
                            'mention_id' => $mention->id,
                            'code' => $pData['code'],
                            'nom' => $pData['nom'],
                            'niveau' => $pData['niveau'],
                            'effectif' => $pData['effectif'],
                        ]);
                    }
                }
            }
        }

        $programmeGL = ProgrammeAcademique::create([
            'annee_academique_id' => $annee->id,
            'code' => 'PA001',
            'nom' => 'Programme GL L1-L3',
            'description' => 'Programme académique Génie Logiciel',
        ]);

        $programmeGL->promotions()->attach([
            Promotion::where('code', 'P001')->value('id'),
            Promotion::where('code', 'P002')->value('id'),
            Promotion::where('code', 'P003')->value('id'),
        ]);

        $programmeRS = ProgrammeAcademique::create([
            'annee_academique_id' => $annee->id,
            'code' => 'PA002',
            'nom' => 'Programme RS L1-L3',
            'description' => 'Programme académique Réseaux et Systèmes',
        ]);

        $programmeRS->promotions()->attach([
            Promotion::where('code', 'P004')->value('id'),
            Promotion::where('code', 'P005')->value('id'),
            Promotion::where('code', 'P006')->value('id'),
        ]);

        $programmeGE = ProgrammeAcademique::create([
            'annee_academique_id' => $annee->id,
            'code' => 'PA003',
            'nom' => 'Programme GE L1-L3',
            'description' => 'Programme académique Gestion des Entreprises',
        ]);

        $programmeGE->promotions()->attach([
            Promotion::where('code', 'P013')->value('id'),
            Promotion::where('code', 'P014')->value('id'),
            Promotion::where('code', 'P015')->value('id'),
        ]);

        $ueGl1 = Ue::create(['programme_academique_id' => $programmeGL->id, 'code' => 'UE001', 'nom' => 'Programmation Web', 'credits' => 6, 'volume_horaire' => 60]);
        $ueGl2 = Ue::create(['programme_academique_id' => $programmeGL->id, 'code' => 'UE002', 'nom' => 'Base de Données', 'credits' => 5, 'volume_horaire' => 45]);
        $ueGl3 = Ue::create(['programme_academique_id' => $programmeGL->id, 'code' => 'UE003', 'nom' => 'Algorithmique', 'credits' => 6, 'volume_horaire' => 60]);

        $ec1 = Ec::create(['ue_id' => $ueGl1->id, 'code' => 'EC001', 'nom' => 'HTML/CSS/JavaScript', 'volume_horaire' => 30, 'statut' => 'Non commencé']);
        $ec2 = Ec::create(['ue_id' => $ueGl1->id, 'code' => 'EC002', 'nom' => 'PHP/Laravel', 'volume_horaire' => 30, 'statut' => 'Non commencé']);
        $ec3 = Ec::create(['ue_id' => $ueGl2->id, 'code' => 'EC003', 'nom' => 'MySQL', 'volume_horaire' => 25, 'statut' => 'Non commencé']);
        $ec4 = Ec::create(['ue_id' => $ueGl2->id, 'code' => 'EC004', 'nom' => 'Modélisation UML', 'volume_horaire' => 20, 'statut' => 'Non commencé']);
        $ec5 = Ec::create(['ue_id' => $ueGl3->id, 'code' => 'EC005', 'nom' => 'Structures de Données', 'volume_horaire' => 30, 'statut' => 'Non commencé']);
        $ec6 = Ec::create(['ue_id' => $ueGl3->id, 'code' => 'EC006', 'nom' => 'Algorithmes Avancés', 'volume_horaire' => 30, 'statut' => 'Non commencé']);

        $ueRs1 = Ue::create(['programme_academique_id' => $programmeRS->id, 'code' => 'UE004', 'nom' => 'Réseaux Informatiques', 'credits' => 5, 'volume_horaire' => 50]);
        $ec7 = Ec::create(['ue_id' => $ueRs1->id, 'code' => 'EC007', 'nom' => 'TCP/IP', 'volume_horaire' => 25, 'statut' => 'Non commencé']);
        $ec8 = Ec::create(['ue_id' => $ueRs1->id, 'code' => 'EC008', 'nom' => 'Administration Réseau', 'volume_horaire' => 25, 'statut' => 'Non commencé']);

        $ueGe1 = Ue::create(['programme_academique_id' => $programmeGE->id, 'code' => 'UE005', 'nom' => 'Comptabilité Générale', 'credits' => 5, 'volume_horaire' => 50]);
        $ec9 = Ec::create(['ue_id' => $ueGe1->id, 'code' => 'EC009', 'nom' => 'Comptabilité Financière', 'volume_horaire' => 25, 'statut' => 'Non commencé']);
        $ec10 = Ec::create(['ue_id' => $ueGe1->id, 'code' => 'EC010', 'nom' => 'Comptabilité Analytique', 'volume_horaire' => 25, 'statut' => 'Non commencé']);

        $enseignantUsers = \App\Models\User::whereHas('role', fn($q) => $q->where('nom', 'Enseignant'))->get();

        $enseignantsData = [
            ['user_id' => null, 'matricule' => 'ENS-0001', 'nom' => 'Mutomb', 'prenom' => 'Jean', 'email' => 'mutomb@universite.cd', 'telephone' => '+243810000001', 'grade' => 'Professeur'],
            ['user_id' => null, 'matricule' => 'ENS-0002', 'nom' => 'Kabongo', 'prenom' => 'Marie', 'email' => 'kabongo@universite.cd', 'telephone' => '+243810000002', 'grade' => 'Maître de conférences'],
            ['user_id' => null, 'matricule' => 'ENS-0003', 'nom' => 'Tshimanga', 'prenom' => 'Pierre', 'email' => 'tshimanga@universite.cd', 'telephone' => '+243810000003', 'grade' => 'Assistant'],
        ];

        foreach ($enseignantsData as $i => $ensData) {
            if (isset($enseignantUsers[$i])) {
                $ensData['user_id'] = $enseignantUsers[$i]->id;
            }
            $ens = Enseignant::create($ensData);

            if ($i === 0) {
                $ens->ecs()->attach([$ec1->id, $ec2->id, $ec5->id]);
            } elseif ($i === 1) {
                $ens->ecs()->attach([$ec3->id, $ec4->id, $ec7->id, $ec8->id]);
            } else {
                $ens->ecs()->attach([$ec6->id, $ec9->id, $ec10->id]);
            }
        }
    }
}
