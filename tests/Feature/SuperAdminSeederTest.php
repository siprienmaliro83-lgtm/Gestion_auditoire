<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_roles_and_super_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Role::count());
        $this->assertNotNull(Role::where('nom', 'Super Administrateur')->first());

        $admin = User::where('email', 'admin@universite.cd')->first();
        $this->assertNotNull($admin);
        $this->assertSame('Super Administrateur', $admin->role?->nom);
        $this->assertTrue($admin->confirme);
        $this->assertTrue(Hash::check('Admin@123456', $admin->password));
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('email', 'admin@universite.cd')->count());
        $this->assertSame(1, Role::where('nom', 'Super Administrateur')->count());
    }

    public function test_super_admin_can_login(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@universite.cd',
            'password' => 'Admin@123456',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_super_admin_can_access_admin_section(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@universite.cd')->first();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Décanats & Admins');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/comptes')
            ->assertOk();
    }

    public function test_regular_admin_can_still_access_admin_section(): void
    {
        $role = Role::factory()->create(['nom' => 'Administrateur']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'confirme' => true,
        ]);

        $this->actingAs($admin)->get('/admin/comptes')->assertOk();
    }

    public function test_login_works_when_matricule_column_is_missing(): void
    {
        $role = Role::factory()->create(['nom' => 'Enseignant']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'confirme' => true,
            'password' => Hash::make('password'),
        ]);

        Schema::table('users', function ($table) {
            $table->dropUnique('users_matricule_unique');
        });
        Schema::dropColumns('users', 'matricule');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_still_works_by_matricule_when_column_exists(): void
    {
        $role = Role::factory()->create(['nom' => 'Étudiant']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'confirme' => true,
            'matricule' => 'ETU-0001',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ETU-0001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
