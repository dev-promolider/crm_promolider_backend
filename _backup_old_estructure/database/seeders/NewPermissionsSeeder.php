<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NewPermissionsSeeder extends Seeder
{
    public function run()
    {
        $role1 = Role::where('name', 'Admin')->first();
        $role2 = Role::where('name', 'Producer')->first();
        $role3 = Role::where('name', 'Distributor')->first();

        Permission::create(['name' => 'report-historial', 'description' => 'My Historial', 'module' => 'reports'])->syncRoles([$role2, $role3]);
        Permission::create(['name' => 'report-proyeccion', 'description' => 'My Proyeccion', 'module' => 'reports'])->syncRoles([$role2, $role3]);
    }
}
