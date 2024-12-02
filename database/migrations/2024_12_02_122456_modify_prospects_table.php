<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('locality')->nullable()->after('country'); // Ajoute locality après la colonne country
            $table->dropColumn('email'); // Supprime la colonne email
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('email')->nullable(); // Réajoute la colonne email
            $table->dropColumn('locality'); // Supprime la colonne locality
        });
    }
}
