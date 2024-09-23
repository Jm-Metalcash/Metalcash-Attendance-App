<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

class AddNewRolesToRolesTable extends Migration
{
    public function up()
    {
        // Insérer les nouveaux rôles dans la table roles
        Role::create(['name' => 'Comptabilité']);
        Role::create(['name' => 'Informatique']);
    }

    public function down()
    {
        // Supprimer les rôles ajoutés lors de la migration
        Role::where('name', 'Comptabilité')->delete();
        Role::where('name', 'Informatique')->delete();
    }
}
