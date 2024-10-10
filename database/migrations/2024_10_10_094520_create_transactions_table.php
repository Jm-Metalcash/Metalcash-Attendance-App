<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historical_transactions', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Clé étrangère non nulle
            $table->date('date')->nullable(); // Date de la transaction, peut être NULL
            $table->enum('type', ['Achat', 'Vente', 'Autre'])->nullable(); // Type de transaction, peut être NULL
            $table->string('typeofmetal', 128)->nullable(); // Type de métal, peut être NULL
            $table->integer('weight')->nullable(); // Poids, peut être NULL
            $table->text('details')->nullable(); // Détails supplémentaires, peut être NULL
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
        Schema::dropIfExists('transactions');
    }
}

