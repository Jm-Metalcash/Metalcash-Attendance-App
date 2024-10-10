<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id(); // Colonne ID int(11), auto-increment, not null
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Relation avec la table clients
            $table->text('content'); // Colonne content pour le texte de la note
            $table->timestamp('note_date')->useCurrent(); // Colonne date (DD-MM-YYYY Heure:minute), par défaut la date actuelle
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
        Schema::dropIfExists('notes');
    }
}