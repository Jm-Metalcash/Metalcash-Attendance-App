<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionRelanceToNotesTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notes_client', function (Blueprint $table) {
            $table->integer('action_relance')->default(0)->after('type');
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
        Schema::table('notes_client', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });

        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->dropColumn('action_relance');
        });
    }
}
