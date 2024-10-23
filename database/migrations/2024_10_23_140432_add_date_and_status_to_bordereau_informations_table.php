<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateAndStatusToBordereauInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bordereau_historique', function (Blueprint $table) {
            // Ajout de la colonne date
            $table->dateTime('date')->nullable()->after('id');

            // Ajout de la colonne status avec une valeur par défaut de 0
            $table->boolean('status')->default(0)->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bordereau_historique', function (Blueprint $table) {
            // Suppression des colonnes ajoutées
            $table->dropColumn('date');
            $table->dropColumn('status');
        });
    }
}
