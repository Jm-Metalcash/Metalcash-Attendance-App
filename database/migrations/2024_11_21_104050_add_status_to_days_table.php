<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToDaysTable extends Migration
{
    public function up()
    {
        Schema::table('days', function (Blueprint $table) {
            $table->boolean('status')->default(0)->after('total');
        });
    }

    public function down()
    {
        Schema::table('days', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
