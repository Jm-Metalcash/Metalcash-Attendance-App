<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateActionRelanceInNotesTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancienne colonne et en créer une nouvelle pour notes_client
        Schema::table('notes_client', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });
        Schema::table('notes_client', function (Blueprint $table) {
            $table->integer('action_relance')->default(0)->after('type');
        });

        // Supprimer l'ancienne colonne et en créer une nouvelle pour notes_prospect
        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });
        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->integer('action_relance')->default(0)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les colonnes originales
        Schema::table('notes_client', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });
        Schema::table('notes_client', function (Blueprint $table) {
            $table->string('action_relance')->nullable()->after('type');
        });

        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });
        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->string('action_relance')->nullable()->after('type');
        });
    }
}
