<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PreregistroPermissionSeeder extends Seeder
{
    public function run()
    {
        $role1 = Role::where('name', 'Admin')->first();
        $role2 = Role::where('name', 'Producer')->first();
        $role3 = Role::where('name', 'Distributor')->first();

        Permission::firstOrCreate([
            'name'        => 'preregistro',
            'description' => 'Preregistro',
            'section'     => 'true',
        ])->syncRoles([$role1, $role2, $role3]);
    }
}
