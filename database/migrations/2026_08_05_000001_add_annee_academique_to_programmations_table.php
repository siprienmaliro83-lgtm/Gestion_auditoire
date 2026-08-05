<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute l'année académique active à chaque programmation.
     */
    public function up(): void
    {
        Schema::table('programmations', function (Blueprint $table) {
            $table->foreignId('annee_academique_id')
                ->nullable()
                ->after('demande_auditoire_id')
                ->constrained('annees_academiques')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programmations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('annee_academique_id');
        });
    }
};
