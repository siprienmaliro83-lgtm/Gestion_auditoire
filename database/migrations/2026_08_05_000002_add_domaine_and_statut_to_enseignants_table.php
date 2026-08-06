<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le domaine de rattachement et le statut à chaque enseignant.
     */
    public function up(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreignId('domaine_id')
                ->nullable()
                ->after('grade')
                ->constrained('domaines')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('statut')->default('Actif')->after('domaine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('domaine_id');
            $table->dropColumn('statut');
        });
    }
};
