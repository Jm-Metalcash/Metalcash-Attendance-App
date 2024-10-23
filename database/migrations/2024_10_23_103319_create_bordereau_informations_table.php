<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBordereauInformationsTable extends Migration
{
    public function up()
    {
        Schema::create('bordereau_informations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bordereau_historique_id');
            $table->string('typeofmetal', 100);
            $table->string('weight', 50);
            $table->text('description')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();

            // Clé étrangère vers la table bordereau_historique
            $table->foreign('bordereau_historique_id')
                ->references('id')
                ->on('bordereau_historique')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bordereau_informations');
    }
}

