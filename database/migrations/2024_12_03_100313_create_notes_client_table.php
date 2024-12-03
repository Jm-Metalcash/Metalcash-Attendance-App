<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesClientTable extends Migration
{
    public function up()
    {
        Schema::create('notes_client', function (Blueprint $table) {
            $table->id(); // id (Primary Key)
            $table->unsignedBigInteger('client_id')->index(); // client_id (Indexed)
            $table->text('content'); // content
            $table->timestamp('note_date'); // note_date
            $table->text('status')->nullable(); // status
            $table->timestamps(); // created_at & updated_at
            $table->string('type', 255)->default('information'); // type

            // Foreign key constraint
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes_client');
    }
}

