<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\Enseignant;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountRequestTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function admin(): User
    {
        return $this->admin ??= User::factory()->create([
            'role_id' => Role::firstOrCreate(['nom' => 'Administrateur'])->id,
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);
    }

    public function test_guest_can_access_account_request_page(): void
    {
        Role::factory()->create(['nom' => 'Décanat']);
        Role::factory()->create(['nom' => 'Administrateur']);

        $this->get('/demander-compte')->assertOk()->assertSee('Demander un compte');
    }

    public function test_guest_can_submit_account_request_as_decanat(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $domaine = Domaine::factory()->create(['nom' => 'Sciences et Technologies']);
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);

        $response = $this->from('/demander-compte')->post('/demander-compte', [
            'name' => 'Décanat Sciences',
            'email' => 'decanat.sciences@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $decanatRole->id,
            'domaine_id' => $domaine->id,
            'filiere_id' => $filiere->id,
            'mention_id' => $mention->id,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'decanat.sciences@universite.cd',
            'role_id' => $decanatRole->id,
            'confirme' => false,
            'domaine_id' => $domaine->id,
            'filiere_id' => $filiere->id,
            'mention_id' => $mention->id,
        ]);
    }

    public function test_decanat_account_request_rejects_filiere_outside_domaine(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $domaineA = Domaine::factory()->create();
        $domaineB = Domaine::factory()->create();
        $filiereOfB = Filiere::factory()->create(['domaine_id' => $domaineB->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiereOfB->id]);

        $response = $this->from('/demander-compte')->post('/demander-compte', [
            'name' => 'Décanat X',
            'email' => 'decanat.x@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $decanatRole->id,
            'domaine_id' => $domaineA->id,
            'filiere_id' => $filiereOfB->id,
            'mention_id' => $mention->id,
        ]);

        $response->assertSessionHasErrors('filiere_id');
        $this->assertDatabaseMissing('users', ['email' => 'decanat.x@universite.cd']);
    }

    public function test_decanat_account_request_rejects_mention_outside_filiere(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $otherFiliere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mentionOfOther = Mention::factory()->create(['filiere_id' => $otherFiliere->id]);

        $response = $this->from('/demander-compte')->post('/demander-compte', [
            'name' => 'Décanat Y',
            'email' => 'decanat.y@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $decanatRole->id,
            'domaine_id' => $domaine->id,
            'filiere_id' => $filiere->id,
            'mention_id' => $mentionOfOther->id,
        ]);

        $response->assertSessionHasErrors('mention_id');
        $this->assertDatabaseMissing('users', ['email' => 'decanat.y@universite.cd']);
    }

    public function test_admin_account_request_does_not_require_domain(): void
    {
        $adminRole = Role::factory()->create(['nom' => 'Administrateur']);

        $response = $this->from('/demander-compte')->post('/demander-compte', [
            'name' => 'Secrétariat',
            'email' => 'secretariat@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $adminRole->id,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'secretariat@universite.cd',
            'role_id' => $adminRole->id,
            'confirme' => false,
            'domaine_id' => null,
            'filiere_id' => null,
            'mention_id' => null,
        ]);
    }

    public function test_decanat_scope_restricts_displayed_data_to_his_mention(): void
    {
        $domaine = Domaine::factory()->create();
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);
        $otherMention = Mention::factory()->create(['filiere_id' => $filiere->id]);

        Promotion::factory()->create(['mention_id' => $mention->id, 'nom' => 'Promotion Ma Mention']);
        Promotion::factory()->create(['mention_id' => $otherMention->id, 'nom' => 'Promotion Autre Mention']);

        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $decanat = User::factory()->create([
            'role_id' => $decanatRole->id,
            'domaine_id' => $domaine->id,
            'filiere_id' => $filiere->id,
            'mention_id' => $mention->id,
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);

        $response = $this->actingAs($decanat)->get('/decanat/promotions');

        $response->assertOk();
        $response->assertSee('Promotion Ma Mention');
        $response->assertDontSee('Promotion Autre Mention');
    }

    public function test_student_role_cannot_be_requested(): void
    {
        $studentRole = Role::factory()->create(['nom' => 'Étudiant']);

        $response = $this->from('/demander-compte')->post('/demander-compte', [
            'name' => 'Étudiant',
            'email' => 'etudiant@universite.cd',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $studentRole->id,
        ]);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['email' => 'etudiant@universite.cd']);
    }

    public function test_pending_account_cannot_login(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        User::factory()->create([
            'role_id' => $decanatRole->id,
            'email' => 'decanat.en.attente@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'decanat.en.attente@universite.cd',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_approve_pending_account(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $pending = User::factory()->create([
            'role_id' => $decanatRole->id,
            'email' => 'decanat.valide@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => false,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/comptes')
            ->assertOk()
            ->assertSee('decanat.valide@universite.cd');

        $this->actingAs($this->admin())->post('/admin/comptes/'.$pending->id.'/approuver')
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $pending->id, 'confirme' => true]);

        $this->post('/login', ['email' => 'decanat.valide@universite.cd', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_admin_can_refuse_and_delete_pending_account(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $pending = User::factory()->create([
            'role_id' => $decanatRole->id,
            'email' => 'decanat.refuse@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => false,
        ]);

        $this->actingAs($this->admin())->post('/admin/comptes/'.$pending->id.'/refuser')
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $pending->id]);
    }

    public function test_admin_can_desactivate_an_active_account(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $active = User::factory()->create([
            'role_id' => $decanatRole->id,
            'email' => 'decanat.actif@universite.cd',
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);

        $this->actingAs($this->admin())->post('/admin/comptes/'.$active->id.'/desactiver')
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $active->id, 'confirme' => false]);
    }

    public function test_admin_cannot_desactivate_his_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/comptes/'.$admin->id.'/desactiver')
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'confirme' => true]);
    }

    public function test_decanat_cannot_access_account_validation(): void
    {
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $decanat = User::factory()->create([
            'role_id' => $decanatRole->id,
            'domaine_id' => Domaine::factory()->create()->id,
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);

        $this->actingAs($decanat)->get('/admin/comptes')->assertForbidden();
    }

    public function test_decanat_reuses_existing_teacher_by_email(): void
    {
        $domaine = Domaine::factory()->create();
        $decanatRole = Role::factory()->create(['nom' => 'Décanat']);
        $decanat = User::factory()->create([
            'role_id' => $decanatRole->id,
            'domaine_id' => $domaine->id,
            'password' => Hash::make('password'),
            'confirme' => true,
        ]);

        $existing = Enseignant::factory()->create([
            'matricule' => 'ENS-EXISTANT',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'dupont.jean@universite.cd',
            'statut' => 'Actif',
        ]);

        $response = $this->actingAs($decanat)->post('/decanat/enseignants', [
            'matricule' => 'ENS-NOUVEAU',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'dupont.jean@universite.cd',
            'grade' => 'Professeur',
            'statut' => 'Actif',
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'enseignants'));

        $this->assertDatabaseCount('enseignants', 1);
        $this->assertDatabaseHas('enseignants', [
            'id' => $existing->id,
            'email' => 'dupont.jean@universite.cd',
            'grade' => 'Professeur',
        ]);
        $this->assertDatabaseMissing('enseignants', ['matricule' => 'ENS-NOUVEAU']);
    }
}
