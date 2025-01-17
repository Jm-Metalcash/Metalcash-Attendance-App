<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notes_client', function (Blueprint $table) {
            $table->text('note_content_status')->nullable()->after('note_date');
        });

        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->text('note_content_status')->nullable()->after('note_date');
        });
    }

    public function down()
    {
        Schema::table('notes_client', function (Blueprint $table) {
            $table->dropColumn('note_content_status');
        });

        Schema::table('notes_prospect', function (Blueprint $table) {
            $table->dropColumn('note_content_status');
        });
    }
};
