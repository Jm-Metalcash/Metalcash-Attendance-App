<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // ID auto-incrémenté
            $table->enum('entity', ['1', '2']); // Entité
            $table->enum('docType', ['1', '2', '3', '4']); // Type de document
            $table->string('docNumber', 64); // Numéro de document
            $table->date('docExp'); // Date d'expiration du document
            $table->string('fullName', 128); // Nom complet
            $table->string('familyName', 64); // Nom de famille
            $table->string('firstName', 64); // Prénom
            $table->date('birthDate')->nullable(); // Date de naissance
            $table->string('address', 128); // Adresse
            $table->string('locality', 128); // Localité
            $table->string('country', 128); // Pays
            $table->string('email', 128)->nullable(); // Adresse e-mail
            $table->string('phone', 64)->nullable(); // Téléphone
            $table->string('company', 64)->nullable(); // Nom de la société
            $table->string('companyvat', 64)->nullable(); // Numéro de TVA
            $table->enum('interest', ['1', '2', '3', '4', '10']); // Intérêt
            $table->enum('referer', ['1', '2', '3', '4', '5']); // Référent
            $table->date('regdate'); // Date d'enregistrement
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
