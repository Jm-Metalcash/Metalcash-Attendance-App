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
        $tables = ['notes_prospect', 'notes_client'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamp('date_relance_modified')->nullable();
                $table->string('old_status_relance')->nullable();
                $table->string('new_status_relance')->nullable();
                $table->unsignedBigInteger('modified_by_relance')->nullable();
                
                $table->foreign('modified_by_relance')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['notes_prospect', 'notes_client'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['modified_by_relance']);
                $table->dropColumn([
                    'date_relance_modified',
                    'old_status_relance',
                    'new_status_relance',
                    'modified_by_relance'
                ]);
            });
        }
    }
};
