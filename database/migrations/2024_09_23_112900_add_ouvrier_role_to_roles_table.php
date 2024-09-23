<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOuvrierRoleToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter le rôle "Ouvrier"
        DB::table('roles')->insert([
            'name' => 'Ouvrier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer le rôle "Ouvrier" en cas de rollback
        DB::table('roles')->where('name', 'Ouvrier')->delete();
    }
}
