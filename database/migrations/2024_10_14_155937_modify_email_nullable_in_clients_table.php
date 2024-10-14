<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEmailNullableInClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email', 128)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email', 128)->nullable(false)->change();
        });
    }
}
