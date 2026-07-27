<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_notifications_index(): void
    {
        $role = Role::factory()->create(['nom' => 'Administrateur']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        Notification::create([
            'id' => (string) str()->uuid(),
            'type' => 'App\\Notifications\\DemandeStatusChanged',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => ['message' => 'Demande traitée'],
        ]);

        $response = $this->actingAs($admin)->get('/admin/notifications');

        $response->assertOk();
        $response->assertSee('Demande traitée');
    }

    public function test_admin_can_export_programmations_report(): void
    {
        $role = Role::factory()->create(['nom' => 'Administrateur']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin)->get('/admin/programmations/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
