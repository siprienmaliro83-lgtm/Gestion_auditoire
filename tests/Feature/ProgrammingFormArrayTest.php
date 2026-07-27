<?php

namespace Tests\Feature;

use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Ue;
use App\Models\User;
use App\Models\ProgrammeAcademique;
use App\Models\AnneeAcademique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProgrammingFormArrayTest extends TestCase
{
    use RefreshDatabase;

    public function test_programming_form_uses_multiple_select_for_promotions_concernees(): void
    {
        $role = Role::factory()->create(['nom' => 'Administrateur']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Admin',
            'email' => 'admin3@example.com',
            'password' => Hash::make('password'),
        ]);

        $annee = AnneeAcademique::factory()->create();
        $programme = ProgrammeAcademique::factory()->create(['annee_academique_id' => $annee->id]);
        $ue = Ue::factory()->create(['programme_academique_id' => $programme->id]);
        $ec = Ec::factory()->create(['ue_id' => $ue->id]);
        $promotionA = Promotion::factory()->create();
        $promotionB = Promotion::factory()->create();
        $batiment = Batiment::factory()->create();
        Auditoire::factory()->create(['batiment_id' => $batiment->id, 'capacite' => 50]);
        Enseignant::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get('/admin/programmations/create');

        $response->assertOk();
        $response->assertSee('name="promotions_concernees[]"', false);
        $response->assertSee('<select', false);
    }
}
