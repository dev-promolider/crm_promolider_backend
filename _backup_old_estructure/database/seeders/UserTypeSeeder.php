<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_type')->insert([
            'id' => 1,
            'type' => 'Administrador'
        ]);
        DB::table('user_type')->insert([
            'id' => 2,
            'type' => 'Productor'
        ]);
        DB::table('user_type')->insert([
            'id' => 3,
            'type' => 'Alumno'
        ]);
    }
}
