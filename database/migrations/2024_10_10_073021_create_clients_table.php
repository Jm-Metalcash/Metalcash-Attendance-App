<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // Colonne ID int(11), auto-increment, not null
            $table->string('fullName', 128)->nullable(); // Colonne fullName varchar(128), peut être null
            $table->string('familyName', 64)->nullable(); // Colonne familyName varchar(64), peut être null
            $table->string('firstName', 64)->nullable(); // Colonne firstName varchar(64), peut être null
            $table->string('address', 128)->nullable(); // Colonne address varchar(128), peut être null
            $table->string('locality', 128)->nullable(); // Colonne locality varchar(128), peut être null
            $table->string('postalCode', 64)->nullable(); // Colonne postalCode varchar(64), peut être null
            $table->string('country', 128)->nullable(); // Colonne country varchar(128), peut être null
            $table->string('email', 128); // Colonne email varchar(128), not null
            $table->string('phone', 64)->unique(); // Colonne phone varchar(64), not null et unique
            $table->string('company', 64)->nullable(); // Colonne company varchar(64), peut être null
            $table->string('companyvat', 64)->nullable(); // Colonne companyvat varchar(64), peut être null
            $table->date('regdate')->nullable(); // Colonne regdate date, peut être null
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}


