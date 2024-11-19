<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('days', function (Blueprint $table) {
            $table->dropColumn('arrival'); // Supprimer la colonne arrival
            $table->dropColumn('departure'); // Supprimer la colonne departure
            $table->dropColumn('break_start'); // Supprimer la colonne break_start
            $table->dropColumn('break_end'); // Supprimer la colonne break_end
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('days', function (Blueprint $table) {
            $table->time('arrival')->nullable(); // Ré-ajouter la colonne arrival
            $table->time('departure')->nullable(); // Ré-ajouter la colonne departure
            $table->time('break_start')->nullable(); // Ré-ajouter la colonne break_start
            $table->time('break_end')->nullable(); // Ré-ajouter la colonne break_end
        });
    }
}

