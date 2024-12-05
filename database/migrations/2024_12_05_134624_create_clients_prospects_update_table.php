<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsProspectsUpdateTable extends Migration
{
    public function up()
    {
        Schema::create('clients_prospects_update', function (Blueprint $table) {
            $table->id();
            $table->string('updatable_type');
            $table->unsignedBigInteger('updatable_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // 'created' ou 'updated'
            $table->timestamps();

            $table->index(['updatable_type', 'updatable_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients_prospects_update');
    }
}
