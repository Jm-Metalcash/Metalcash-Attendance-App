<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Utiliser firstOrCreate pour éviter la duplication en cas de rôles déjà existants
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Employé']);
        Role::firstOrCreate(['name' => 'Comptabilité']);
        Role::firstOrCreate(['name' => 'Informatique']);
        Role::firstOrCreate(['name' => 'Ouvrier']);
    }
}
