<?php

namespace Tests\Feature;

use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Notification;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttributionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_attribution_creates_notifications_for_teacher_and_students(): void
    {
        $adminRole = Role::factory()->create(['nom' => 'Administrateur']);
        $teacherRole = Role::factory()->create(['nom' => 'Enseignant']);
        $studentRole = Role::factory()->create(['nom' => 'Étudiant']);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'name' => 'Admin',
            'email' => 'admin3@example.com',
            'password' => Hash::make('password'),
        ]);

        $teacherUser = User::factory()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Prof',
            'email' => 'teacher3@example.com',
            'password' => Hash::make('password'),
        ]);

        $teacher = Enseignant::factory()->create([
            'user_id' => $teacherUser->id,
            'email' => $teacherUser->email,
        ]);

        $promotion = Promotion::factory()->create();
        $studentUser = User::factory()->create([
            'role_id' => $studentRole->id,
            'promotion_id' => $promotion->id,
            'name' => 'Student',
            'email' => 'student3@example.com',
            'password' => Hash::make('password'),
        ]);

        $ec = Ec::factory()->create();
        $batiment = Batiment::factory()->create();
        $auditoire = Auditoire::factory()->create([
            'batiment_id' => $batiment->id,
            'capacite' => 200,
            'etat' => 'Disponible',
        ]);

        $demande = DemandeAuditoire::factory()->create([
            'user_id' => $admin->id,
            'ec_id' => $ec->id,
            'enseignant_id' => $teacher->id,
            'promotions_concernees' => [$promotion->id],
            'date_debut' => now()->addDay()->toDateString(),
            'date_fin' => now()->addDay()->toDateString(),
            'heure_debut' => '08:00:00',
            'heure_fin' => '10:00:00',
            'effectif_total' => 50,
            'statut' => 'Acceptée',
        ]);

        $response = $this->actingAs($admin)->post('/admin/attributions', [
            'demande_auditoire_id' => $demande->id,
            'auditoire_id' => $auditoire->id,
            'statut' => 'Validée',
        ]);

        $response->assertRedirect('/admin/attributions/'.$demande->id);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $teacherUser->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $studentUser->id,
        ]);
    }
}
