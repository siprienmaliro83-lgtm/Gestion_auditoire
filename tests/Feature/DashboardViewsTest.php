<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\Enseignant;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardViewsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::factory()->create(['nom' => $role])->id,
            'password' => Hash::make('password'),
        ], $extra));
    }

    public function test_admin_dashboard_renders(): void
    {
        $response = $this->actingAs($this->user('Administrateur'))->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Demandes récentes');
    }

    public function test_decanat_dashboard_renders(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->user('Décanat', ['domaine_id' => $domaine->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Nouvelle demande');
    }

    public function test_enseignant_dashboard_renders(): void
    {
        $user = $this->user('Enseignant');
        Enseignant::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Mes EC');
    }

    public function test_enseignant_programmations_renders(): void
    {
        $user = $this->user('Enseignant');
        Enseignant::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/enseignant/programmations');

        $response->assertOk();
        $response->assertSee('Mon horaire de cours');
    }

    public function test_etudiant_dashboard_renders(): void
    {
        $promotion = Promotion::factory()->create();
        $user = $this->user('Étudiant', ['promotion_id' => $promotion->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Mon emploi du temps');
    }

    public function test_etudiant_programmations_renders(): void
    {
        $promotion = Promotion::factory()->create();
        $user = $this->user('Étudiant', ['promotion_id' => $promotion->id]);

        $response = $this->actingAs($user)->get('/etudiant/programmations');

        $response->assertOk();
        $response->assertSee('Programme de ma promotion');
    }
}

