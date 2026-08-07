<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttributionPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::factory()->create(['nom' => 'Administrateur']);

        return User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Admin',
            'email' => 'admin-pages@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function demande(string $statut = 'En attente'): DemandeAuditoire
    {
        $teacherUser = User::factory()->create([
            'role_id' => Role::factory()->create(['nom' => 'Enseignant'])->id,
        ]);
        $teacher = Enseignant::factory()->create(['user_id' => $teacherUser->id]);

        $demandeur = User::factory()->create([
            'role_id' => Role::factory()->create(['nom' => 'Décanat'])->id,
        ]);

        return DemandeAuditoire::factory()->create([
            'user_id' => $demandeur->id,
            'ec_id' => Ec::factory()->create(['nom' => 'Comptabilité Financière'])->id,
            'enseignant_id' => $teacher->id,
            'promotions_concernees' => [Promotion::factory()->create()->id],
            'effectif_total' => 30,
            'statut' => $statut,
        ]);
    }

    public function test_admin_can_view_attributions_index(): void
    {
        $this->demande();

        $response = $this->actingAs($this->admin())->get('/admin/attributions');

        $response->assertOk();
        $response->assertSee('Attribuer');
        $response->assertSee('Comptabilité Financière');
    }

    public function test_admin_can_view_demande_detail(): void
    {
        $demande = $this->demande('Acceptée');

        $response = $this->actingAs($this->admin())->get('/admin/attributions/'.$demande->id);

        $response->assertOk();
        $response->assertSee('Attribuer un auditoire');
    }

    public function test_admin_can_reject_demande_with_motif(): void
    {
        $demande = $this->demande();

        $response = $this->actingAs($this->admin())->post('/admin/attributions/'.$demande->id.'/rejeter', [
            'motif_refus' => 'Conflit de planning sur toute la période.',
        ]);

        $response->assertRedirect('/admin/attributions');
        $this->assertDatabaseHas('demandes_auditoire', [
            'id' => $demande->id,
            'statut' => 'Refusée',
        ]);
        $this->assertSame('Conflit de planning sur toute la période.', $demande->fresh()->motif_refus);
    }

    public function test_admin_cannot_assign_disponibility_conflict_auditoire(): void
    {
        $demande = $this->demande('Acceptée');
        AnneeAcademique::factory()->create(['active' => true]);
        $batiment = Batiment::factory()->create();
        $auditoire = Auditoire::factory()->create([
            'batiment_id' => $batiment->id,
            'capacite' => 100,
            'etat' => 'Maintenance',
        ]);

        $response = $this->actingAs($this->admin())->post('/admin/attributions', [
            'demande_auditoire_id' => $demande->id,
            'auditoire_id' => $auditoire->id,
        ]);

        $response->assertSessionHasErrors('auditoire_id');
        $this->assertDatabaseMissing('programmations', [
            'demande_auditoire_id' => $demande->id,
        ]);
        $this->assertSame('Acceptée', $demande->fresh()->statut);
    }
}
