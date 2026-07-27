<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domaines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('filieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domaine_id')->constrained('domaines')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['domaine_id', 'nom']);
        });

        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filiere_id')->constrained('filieres')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['filiere_id', 'nom']);
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mention_id')->constrained('mentions')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->unsignedInteger('niveau');
            $table->unsignedInteger('effectif')->default(0);
            $table->timestamps();

            $table->unique(['mention_id', 'nom']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('domaine_id')->references('id')->on('domaines')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('promotion_id')->references('id')->on('promotions')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('annees_academiques', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('programmes_academiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annee_academique_id')->constrained('annees_academiques')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['annee_academique_id', 'nom']);
        });

        Schema::create('programme_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_academique_id')->constrained('programmes_academiques')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['programme_academique_id', 'promotion_id']);
        });

        Schema::create('ues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_academique_id')->constrained('programmes_academiques')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->unsignedInteger('credits')->default(0);
            $table->unsignedInteger('volume_horaire')->default(0);
            $table->timestamps();

            $table->unique(['programme_academique_id', 'nom']);
        });

        Schema::create('ecs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ue_id')->constrained('ues')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('nom');
            $table->unsignedInteger('volume_horaire');
            $table->enum('statut', ['Non commencé', 'En cours', 'Entièrement dispensé'])->default('Non commencé');
            $table->timestamps();

            $table->unique(['ue_id', 'nom']);
        });

        Schema::create('enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('grade')->nullable();
            $table->timestamps();
        });

        Schema::create('enseignant_ec', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enseignant_id')->constrained('enseignants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('ec_id')->constrained('ecs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['enseignant_id', 'ec_id']);
        });

        Schema::create('batiments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom')->unique();
            $table->string('localisation')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('auditoires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batiment_id')->constrained('batiments')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nom');
            $table->unsignedInteger('capacite');
            $table->enum('etat', ['Disponible', 'Indisponible', 'Maintenance'])->default('Disponible');
            $table->timestamps();

            $table->unique(['batiment_id', 'nom']);
        });

        Schema::create('demandes_auditoire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('ec_id')->constrained('ecs')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('enseignant_id')->constrained('enseignants')->cascadeOnUpdate()->restrictOnDelete();
            $table->json('promotions_concernees');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->unsignedInteger('effectif_total');
            $table->enum('statut', ['En attente', 'Acceptée', 'Refusée', 'Attribuée'])->default('En attente');
            $table->text('motif_refus')->nullable();
            $table->timestamp('envoyee_a')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_debut', 'heure_debut', 'heure_fin']);
        });

        Schema::create('programmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_auditoire_id')->nullable()->constrained('demandes_auditoire')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('ec_id')->constrained('ecs')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('enseignant_id')->constrained('enseignants')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('auditoire_id')->constrained('auditoires')->cascadeOnUpdate()->restrictOnDelete();
            $table->json('promotions_concernees');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->unsignedInteger('effectif_total');
            $table->enum('statut', ['Brouillon', 'Validée', 'Annulée'])->default('Validée');
            $table->foreignId('validee_par')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('validee_a')->nullable();
            $table->timestamps();

            $table->index(['auditoire_id', 'date_debut', 'heure_debut', 'heure_fin', 'statut']);
            $table->index(['enseignant_id', 'date_debut', 'heure_debut', 'heure_fin', 'statut']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('programmations');
        Schema::dropIfExists('demandes_auditoire');
        Schema::dropIfExists('auditoires');
        Schema::dropIfExists('batiments');
        Schema::dropIfExists('enseignant_ec');
        Schema::dropIfExists('enseignants');
        Schema::dropIfExists('ecs');
        Schema::dropIfExists('ues');
        Schema::dropIfExists('programme_promotion');
        Schema::dropIfExists('programmes_academiques');
        Schema::dropIfExists('annees_academiques');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['domaine_id']);
            $table->dropForeign(['promotion_id']);
        });

        Schema::dropIfExists('promotions');
        Schema::dropIfExists('mentions');
        Schema::dropIfExists('filieres');
        Schema::dropIfExists('domaines');
    }
};
