<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\ProgrammeAcademique;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DecanatCrudTest extends TestCase
{
    use RefreshDatabase;

    private function decanatUser(Domaine $domaine): User
    {
        return User::factory()->create([
            'role_id' => Role::factory()->create(['nom' => 'Décanat'])->id,
            'domaine_id' => $domaine->id,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_decanat_can_access_academic_crud_pages(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        foreach (['domaines', 'filieres', 'mentions', 'promotions', 'programmes-academiques', 'ues', 'ecs'] as $resource) {
            $this->actingAs($user)->get('/decanat/'.$resource)->assertOk();
        }
    }

    public function test_decanat_can_access_create_forms(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        foreach (['filieres', 'mentions', 'promotions', 'programmes-academiques', 'ues', 'ecs'] as $resource) {
            $this->actingAs($user)->get('/decanat/'.$resource.'/create')->assertOk();
        }
    }

    public function test_decanat_crud_index_is_filtered_by_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $other = Domaine::factory()->create();

        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        Promotion::factory()->create(['mention_id' => $mention->id, 'nom' => 'Promotion Domaine A']);

        $otherFiliere = Filiere::factory()->create(['domaine_id' => $other->id]);
        $otherMention = Mention::factory()->create(['filiere_id' => $otherFiliere->id]);
        Promotion::factory()->create(['mention_id' => $otherMention->id, 'nom' => 'Promotion Domaine B']);

        $response = $this->actingAs($this->decanatUser($domaine))->get('/decanat/promotions');

        $response->assertOk();
        $response->assertSee('Promotion Domaine A');
        $response->assertDontSee('Promotion Domaine B');
    }

    public function test_decanat_can_create_promotion_in_his_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->post('/decanat/promotions', [
            'mention_id' => $mention->id,
            'code' => 'P099',
            'nom' => 'L1 Nouvelle',
            'niveau' => 1,
            'effectif' => 50,
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'promotions'));
        $this->assertDatabaseHas('promotions', ['code' => 'P099', 'mention_id' => $mention->id]);
    }

    public function test_decanat_cannot_create_promotion_outside_his_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $other = Domaine::factory()->create();
        $otherFiliere = Filiere::factory()->create(['domaine_id' => $other->id]);
        $otherMention = Mention::factory()->create(['filiere_id' => $otherFiliere->id]);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->from('/decanat/promotions/create')->post('/decanat/promotions', [
            'mention_id' => $otherMention->id,
            'code' => 'P100',
            'nom' => 'L1 Hors domaine',
            'niveau' => 1,
            'effectif' => 50,
        ]);

        $response->assertSessionHasErrors('mention_id');
        $this->assertDatabaseMissing('promotions', ['code' => 'P100']);
    }

    public function test_decanat_can_create_programme_linked_to_domain_promotions(): void
    {
        $annee = AnneeAcademique::factory()->create();
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        $promotion = Promotion::factory()->create(['mention_id' => $mention->id]);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->post('/decanat/programmes-academiques', [
            'annee_academique_id' => $annee->id,
            'code' => 'PA999',
            'nom' => 'Programme test',
            'description' => null,
            'promotions' => [$promotion->id],
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'programmes-academiques'));
        $this->assertDatabaseHas('programmes_academiques', ['code' => 'PA999']);

        $programmeId = ProgrammeAcademique::where('code', 'PA999')->value('id');
        $this->assertDatabaseHas('programme_promotion', [
            'programme_academique_id' => $programmeId,
            'promotion_id' => $promotion->id,
        ]);
    }

    public function test_admin_cannot_access_decanat_crud(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::factory()->create(['nom' => 'Administrateur'])->id,
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)->get('/decanat/promotions')->assertForbidden();
    }
}
