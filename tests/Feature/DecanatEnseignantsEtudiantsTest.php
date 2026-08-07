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

class DecanatEnseignantsEtudiantsTest extends TestCase
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

    private function studentRole(): Role
    {
        return Role::firstOrCreate(['nom' => 'Étudiant']);
    }

    private function promotionIn(Domaine $domaine): Promotion
    {
        $filiere = Filiere::factory()->create(['domaine_id' => $domaine->id]);
        $mention = Mention::factory()->create(['filiere_id' => $filiere->id]);

        return Promotion::factory()->create(['mention_id' => $mention->id]);
    }

    public function test_decanat_can_access_enseignants_and_etudiants_pages(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();

        foreach (['enseignants', 'etudiants'] as $resource) {
            $this->actingAs($this->decanatUser($domaine))->get('/decanat/'.$resource)->assertOk();
            $this->actingAs($this->decanatUser($domaine))->get('/decanat/'.$resource.'/create')->assertOk();
        }
    }

    public function test_decanat_can_create_enseignant_and_his_login_account(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);
        $enseignantRole = Role::firstOrCreate(['nom' => 'Enseignant']);

        $response = $this->actingAs($user)->post('/decanat/enseignants', [
            'matricule' => 'ENS-1001',
            'nom' => 'Dupont Jean',
            'email' => 'jean.dupont@universite.cd',
            'telephone' => '+243900000000',
            'grade' => 'Professeur',
            'specialite' => 'Réseaux',
            'statut' => 'Actif',
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'enseignants'));
        $this->assertDatabaseHas('enseignants', [
            'matricule' => 'ENS-1001',
            'nom' => 'Dupont Jean',
            'email' => 'jean.dupont@universite.cd',
            'grade' => 'Professeur',
            'specialite' => 'Réseaux',
            'statut' => 'Actif',
        ]);

        $enseignant = Enseignant::where('matricule', 'ENS-1001')->first();
        $this->assertNotNull($enseignant->user_id);
        $this->assertDatabaseHas('users', [
            'id' => $enseignant->user_id,
            'name' => 'Dupont Jean',
            'email' => 'jean.dupont@universite.cd',
            'role_id' => $enseignantRole->id,
            'confirme' => true,
        ]);

        $userAccount = User::find($enseignant->user_id);
        $this->assertTrue(Hash::check('ENS-1001', $userAccount->password));
    }

    public function test_decanat_cannot_create_enseignant_with_duplicate_email(): void
    {
        $domaine = Domaine::factory()->create();
        $user = $this->decanatUser($domaine);

        Enseignant::factory()->create([
            'matricule' => 'ENS-EXISTANT',
            'email' => 'dupont.jean@universite.cd',
        ]);

        $response = $this->actingAs($user)->from('/decanat/enseignants/create')->post('/decanat/enseignants', [
            'matricule' => 'ENS-NOUVEAU',
            'nom' => 'Dupont Jean',
            'email' => 'dupont.jean@universite.cd',
            'grade' => 'Professeur',
            'statut' => 'Actif',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('enseignants', ['matricule' => 'ENS-NOUVEAU']);
    }

    public function test_enseignant_list_is_universal_across_decanats(): void
    {
        $domaineST = Domaine::factory()->create();
        $domaineSEG = Domaine::factory()->create();
        $this->studentRole();

        $enseignantSEG = Enseignant::factory()->create([
            'nom' => 'Enseignant SEG',
            'domaine_id' => $domaineSEG->id,
        ]);

        $response = $this->actingAs($this->decanatUser($domaineST))->get('/decanat/enseignants');

        $response->assertOk();
        $response->assertSee('Enseignant SEG');
    }

    public function test_decanat_can_create_student_in_his_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();
        $promotion = $this->promotionIn($domaine);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->post('/decanat/etudiants', [
            'matricule' => 'ETU-2025',
            'name' => 'Jean Kasongo',
            'email' => 'jean.kasongo@etudiant.universite.cd',
            'password' => 'password123',
            'promotion_id' => $promotion->id,
        ]);

        $response->assertRedirect(route('decanat.crud.index', 'etudiants'));
        $this->assertDatabaseHas('users', [
            'matricule' => 'ETU-2025',
            'email' => 'jean.kasongo@etudiant.universite.cd',
            'role_id' => $this->studentRole()->id,
            'promotion_id' => $promotion->id,
            'confirme' => true,
        ]);
        $this->assertNotSame('password123', User::where('matricule', 'ETU-2025')->value('password'));
    }

    public function test_decanat_cannot_create_student_outside_his_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $other = Domaine::factory()->create();
        $this->studentRole();
        $otherPromotion = $this->promotionIn($other);
        $user = $this->decanatUser($domaine);

        $response = $this->actingAs($user)->from('/decanat/etudiants/create')->post('/decanat/etudiants', [
            'matricule' => 'ETU-3000',
            'name' => 'Autre Étudiant',
            'email' => 'autre.etudiant@etudiant.universite.cd',
            'password' => 'password123',
            'promotion_id' => $otherPromotion->id,
        ]);

        $response->assertSessionHasErrors('promotion_id');
        $this->assertDatabaseMissing('users', ['matricule' => 'ETU-3000']);
    }

    public function test_etudiant_index_is_filtered_by_decanat_domain(): void
    {
        $domaine = Domaine::factory()->create();
        $other = Domaine::factory()->create();
        $this->studentRole();

        $promotion = $this->promotionIn($domaine);
        $otherPromotion = $this->promotionIn($other);

        User::factory()->create([
            'role_id' => $this->studentRole()->id,
            'name' => 'Étudiant Du Domaine',
            'matricule' => 'ETU-0001',
            'promotion_id' => $promotion->id,
            'confirme' => true,
        ]);
        User::factory()->create([
            'role_id' => $this->studentRole()->id,
            'name' => 'Étudiant Autre Domaine',
            'matricule' => 'ETU-0002',
            'promotion_id' => $otherPromotion->id,
            'confirme' => true,
        ]);

        $response = $this->actingAs($this->decanatUser($domaine))->get('/decanat/etudiants');

        $response->assertOk();
        $response->assertSee('Étudiant Du Domaine');
        $response->assertDontSee('Étudiant Autre Domaine');
    }

    public function test_demande_form_lists_all_active_teachers(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();

        Enseignant::factory()->create(['nom' => 'Enseignant Actif', 'statut' => 'Actif']);
        Enseignant::factory()->create(['nom' => 'Enseignant Inactif', 'statut' => 'Inactif']);

        $response = $this->actingAs($this->decanatUser($domaine))->get('/decanat/demandes/create');

        $response->assertOk();
        $response->assertSee('Enseignant Actif');
        $response->assertDontSee('Enseignant Inactif');
    }

    public function test_student_can_login_with_matricule(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();
        $promotion = $this->promotionIn($domaine);

        $student = User::factory()->create([
            'role_id' => $this->studentRole()->id,
            'name' => 'Kasongo Jean',
            'matricule' => 'ETU-0101',
            'password' => Hash::make('password'),
            'promotion_id' => $promotion->id,
            'confirme' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'ETU-0101',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);
    }

    public function test_student_can_login_with_name(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();
        $promotion = $this->promotionIn($domaine);

        $student = User::factory()->create([
            'role_id' => $this->studentRole()->id,
            'name' => 'Kasongo Jean',
            'matricule' => 'ETU-0102',
            'password' => Hash::make('password'),
            'promotion_id' => $promotion->id,
            'confirme' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'Kasongo Jean',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);
    }

    public function test_teacher_can_login_with_email_and_matricule_password(): void
    {
        $teacherRole = Role::factory()->create(['nom' => 'Enseignant']);
        $teacher = User::factory()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Mukendi Paul',
            'email' => 'paul.mukendi@universite.cd',
            'password' => Hash::make('ENS-2025'),
            'confirme' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'paul.mukendi@universite.cd',
            'password' => 'ENS-2025',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_teacher_can_login_with_full_name_and_matricule_password(): void
    {
        $teacherRole = Role::factory()->create(['nom' => 'Enseignant']);
        $teacher = User::factory()->create([
            'role_id' => $teacherRole->id,
            'name' => 'Mukendi Paul',
            'email' => 'paul.mukendi@universite.cd',
            'password' => Hash::make('ENS-2025'),
            'confirme' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'Mukendi Paul',
            'password' => 'ENS-2025',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($teacher);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $this->studentRole();

        $response = $this->post('/login', [
            'email' => 'ETU-0000',
            'password' => 'mauvais',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_blocks_unconfirmed_student(): void
    {
        $domaine = Domaine::factory()->create();
        $this->studentRole();
        $promotion = $this->promotionIn($domaine);

        User::factory()->create([
            'role_id' => $this->studentRole()->id,
            'name' => 'En Attente',
            'matricule' => 'ETU-0999',
            'password' => Hash::make('password'),
            'promotion_id' => $promotion->id,
            'confirme' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'ETU-0999',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
