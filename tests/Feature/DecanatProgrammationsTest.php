<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Domaine;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DecanatProgrammationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_decanat_can_view_programmations_index(): void
    {
        $role = Role::factory()->create(['nom' => 'Décanat']);
        $domaine = Domaine::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'domaine_id' => $domaine->id,
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get('/decanat/programmations');

        $response->assertOk();
        $response->assertSee('Horaires');
    }
}
