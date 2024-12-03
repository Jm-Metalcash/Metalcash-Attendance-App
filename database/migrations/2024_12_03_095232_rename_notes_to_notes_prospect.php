<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameNotesToNotesProspect extends Migration
{
    public function up()
    {
        Schema::rename('notes', 'notes_prospect');
    }

    public function down()
    {
        Schema::rename('notes_prospect', 'notes');
    }
}

