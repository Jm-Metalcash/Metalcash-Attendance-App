<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('days', function (Blueprint $table) {
        $table->time('break_start')->nullable();
        $table->time('break_end')->nullable();
    });
}

public function down()
{
    Schema::table('days', function (Blueprint $table) {
        $table->dropColumn(['break_start', 'break_end']);
    });
}

};
