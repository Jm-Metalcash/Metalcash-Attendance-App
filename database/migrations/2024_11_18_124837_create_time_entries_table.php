<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liaison avec la table users
            $table->foreignId('day_id')->constrained('days')->onDelete('cascade'); // Liaison avec la table days
            $table->enum('type', ['arrival', 'departure']); // Type d'entrée : arrivée ou départ
            $table->time('time'); // Heure d'arrivée ou de départ
            $table->timestamps(); // Champs created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_entries');
    }
}

