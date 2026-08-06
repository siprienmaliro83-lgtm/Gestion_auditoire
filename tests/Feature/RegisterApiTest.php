<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_registration_is_disabled(): void
    {
        Role::factory()->create(['nom' => 'Étudiant']);
        Domaine::factory()->create();

        $this->get('/register')->assertNotFound();
    }

    public function test_student_cannot_self_register_via_post(): void
    {
        $role = Role::factory()->create(['nom' => 'Étudiant']);
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        $promotion = Promotion::factory()->create(['mention_id' => $mention->id]);

        $this->post('/register', [
            'name' => 'Nouvel Étudiant',
            'email' => 'etudiant.nouveau@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $role->id,
            'promotion_id' => $promotion->id,
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'etudiant.nouveau@universite.cd']);
    }

    public function test_api_returns_filieres_of_domaine(): void
    {
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $other = Filiere::factory()->create();

        $response = $this->getJson('/api/filieres?domaine_id='.$domaine->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $filiere->id]);
        $response->assertJsonMissing(['id' => $other->id]);
    }

    public function test_api_returns_mentions_of_filiere(): void
    {
        $filiere = Filiere::factory()->create();
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        $other = Mention::factory()->create();

        $response = $this->getJson('/api/mentions?filiere_id='.$filiere->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $mention->id]);
        $response->assertJsonMissing(['id' => $other->id]);
    }

    public function test_api_returns_promotions_of_mention(): void
    {
        $mention = Mention::factory()->create();
        $promotion = Promotion::factory()->create(['mention_id' => $mention->id]);
        $other = Promotion::factory()->create();

        $response = $this->getJson('/api/promotions?mention_id='.$mention->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $promotion->id]);
        $response->assertJsonMissing(['id' => $other->id]);
    }

    public function test_api_validates_required_parent(): void
    {
        $this->getJson('/api/filieres')->assertUnprocessable();
    }
}
