<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\DemandeAuditoire;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\ProgrammeAcademique;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use App\Services\ProgrammationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DecanatAnneesAcademiquesTest extends TestCase
{
    use RefreshDatabase;

    private function decanatUser(Domaine $domaine): User
    {
        return User::factory()->create([
            'role_id' => Role::firstOrCreate(['nom' => 'Décanat'])->id,
            'domaine_id' => $domaine->id,
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);
    }

    private function promotionIn(Domaine $domaine): Promotion
    {
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);

        return Promotion::factory()->create(['mention_id' => $mention->id]);
    }

    private function demandeEnAttente(): DemandeAuditoire
    {
        $decanatRole = Role::firstOrCreate(['nom' => 'Décanat']);
        $demandeur = User::factory()->create(['role_id' => $decanatRole->id]);
        $enseignantRole = Role::firstOrCreate(['nom' => 'Enseignant']);
        $teacherUser = User::factory()->create(['role_id' => $enseignantRole->id]);
        $teacher = Enseignant::factory()->create(['user_id' => $teacherUser->id]);

        return DemandeAuditoire::factory()->create([
            'user_id' => $demandeur->id,
            'ec_id' => Ec::factory()->create()->id,
            'enseignant_id' => $teacher->id,
            'statut' => 'En attente',
        ]);
    }

    public function test_decanat_can_access_annees_academiques_pages(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        $this->actingAs($user)->get('/decanat/annees-academiques')->assertOk();
        $this->actingAs($user)->get('/decanat/annees-academiques/create')->assertOk();
    }

    public function test_decanat_can_create_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->post('/decanat/annees-academiques', [
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-08-31',
            'active' => 1,
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'annees-academiques'));
        $this->assertDatabaseHas('annees_academiques', [
            'libelle' => '2025-2026',
            'active' => true,
        ]);
    }

    public function test_decanat_cannot_activate_two_annees_academiques(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        AnneeAcademique::factory()->create(['libelle' => '2024-2025', 'active' => true]);

        $response = $this->actingAs($user)->from('/decanat/annees-academiques/create')->post('/decanat/annees-academiques', [
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-08-31',
            'active' => 1,
        ]);

        $response->assertSessionHasErrors('active');
        $this->assertDatabaseMissing('annees_academiques', ['libelle' => '2025-2026']);
    }

    public function test_decanat_can_deactivate_and_activate_another_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        $current = AnneeAcademique::factory()->create(['libelle' => '2024-2025', 'active' => true]);
        $next = AnneeAcademique::factory()->create(['libelle' => '2025-2026', 'active' => false]);

        $this->actingAs($user)->put('/decanat/annees-academiques/'.$current->id, [
            'libelle' => '2024-2025',
            'date_debut' => '2024-09-01',
            'date_fin' => '2025-08-31',
            'active' => 0,
        ])->assertRedirect(route('decanat.crud.index', 'annees-academiques'));

        $response = $this->put('/decanat/annees-academiques/'.$next->id, [
            'libelle' => '2025-2026',
            'date_debut' => '2025-09-01',
            'date_fin' => '2026-08-31',
            'active' => 1,
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'annees-academiques'));
        $this->assertTrue($current->fresh()->active === false);
        $this->assertTrue($next->fresh()->active === true);
    }

    public function test_decanat_cannot_delete_annee_academique_used_by_programme(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        $annee = AnneeAcademique::factory()->create(['active' => true]);
        ProgrammeAcademique::factory()->create(['annee_academique_id' => $annee->id]);

        $response = $this->actingAs($user)->from('/decanat/annees-academiques')->delete('/decanat/annees-academiques/'.$annee->id);

        $response->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('annees_academiques', ['id' => $annee->id]);
    }

    public function test_demande_creation_is_blocked_without_active_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $promotion = $this->promotionIn($domaine);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->from('/decanat/demandes/create')->post('/decanat/demandes', [
            'ec_id' => Ec::factory()->create()->id,
            'enseignant_id' => Enseignant::factory()->create()->id,
            'promotions_concernees' => [$promotion->id],
            'date_debut' => '2025-10-01',
            'date_fin' => '2025-10-01',
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'effectif_total' => 50,
        ]);

        $response->assertSessionHasErrors('annee_academique');
        $this->assertDatabaseCount('demandes_auditoire', 0);
    }

    public function test_demande_creation_works_with_active_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $promotion = $this->promotionIn($domaine);
        $user = $this->decanatUser($domaine);
        AnneeAcademique::factory()->create(['active' => true]);

        $response = $this->actingAs($user)->post('/decanat/demandes', [
            'ec_id' => Ec::factory()->create()->id,
            'enseignant_id' => Enseignant::factory()->create()->id,
            'promotions_concernees' => [$promotion->id],
            'date_debut' => '2025-10-01',
            'date_fin' => '2025-10-01',
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'effectif_total' => 50,
        ]);

        $response->assertRedirect(route('decanat.demandes.index'));
        $this->assertDatabaseCount('demandes_auditoire', 1);
    }

    public function test_attribution_is_blocked_without_active_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $validateur = $this->decanatUser($domaine);
        $demande = $this->demandeEnAttente();
        $auditoire = Auditoire::factory()->create(['etat' => 'Disponible', 'capacite' => 1000]);

        try {
            app(ProgrammationService::class)->attribuer($demande, $auditoire, $validateur);
            $this->fail('Une ValidationException aurait dû être levée.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('annee_academique', $e->errors());
        }

        $this->assertDatabaseCount('programmations', 0);
    }

    public function test_attribution_associates_the_active_annee_academique(): void
    {
        $domaine = Domaine::factory()->create();
        $validateur = $this->decanatUser($domaine);
        $annee = AnneeAcademique::factory()->create(['active' => true]);
        $demande = $this->demandeEnAttente();
        $auditoire = Auditoire::factory()->create(['etat' => 'Disponible', 'capacite' => 1000]);

        $programmation = app(ProgrammationService::class)->attribuer($demande, $auditoire, $validateur);

        $this->assertSame($annee->id, $programmation->annee_academique_id);
        $this->assertSame('Attribuée', $demande->fresh()->statut);
    }
}
