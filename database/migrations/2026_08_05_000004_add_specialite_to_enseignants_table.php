<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la spécialité à chaque enseignant.
     */
    public function up(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->string('specialite')->nullable()->after('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropColumn('specialite');
        });
    }
};
