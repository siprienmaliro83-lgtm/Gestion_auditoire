<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rattache le compte Décanat à sa Filière et à sa Mention.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('filiere_id')->nullable()->after('domaine_id')->index();
            $table->unsignedBigInteger('mention_id')->nullable()->after('filiere_id')->index();

            $table->foreign('filiere_id')
                ->references('id')
                ->on('filieres')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('mention_id')
                ->references('id')
                ->on('mentions')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['filiere_id']);
            $table->dropForeign(['mention_id']);
            $table->dropColumn(['filiere_id', 'mention_id']);
        });
    }
};
