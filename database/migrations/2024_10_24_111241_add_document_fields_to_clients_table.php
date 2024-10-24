<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentFieldsToClientsTable extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('entity')->nullable();
            $table->string('docType')->nullable();
            $table->string('docNumber')->nullable();
            $table->date('docExp')->nullable();
            $table->string('interest')->nullable();
            $table->string('referer')->nullable();
            $table->date('birthDate')->nullable();
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['entity', 'docType', 'docNumber', 'docExp', 'interest', 'referer', 'birthDate']);
        });
    }
}
