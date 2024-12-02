<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedColumnsFromProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'locality',
                'postalCode',
                'company',
                'companyvat',
                'entity',
                'docType',
                'regdate',
                'docNumber',
                'docExp',
                'interest',
                'referer',
                'birthDate',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('address')->nullable();
            $table->string('locality')->nullable();
            $table->string('postalCode', 10)->nullable();
            $table->string('company')->nullable();
            $table->string('companyvat')->nullable();
            $table->string('entity')->nullable();
            $table->string('docType')->nullable();
            $table->date('regdate')->nullable();
            $table->string('docNumber')->nullable();
            $table->date('docExp')->nullable();
            $table->string('interest')->nullable();
            $table->string('referer')->nullable();
            $table->date('birthDate')->nullable();
        });
    }
}

