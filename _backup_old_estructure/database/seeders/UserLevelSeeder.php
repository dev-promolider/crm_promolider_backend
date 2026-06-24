<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_levels')->insert([
            'description' => 'CALCITA',
            'experience_required' => 0,
        ]);
        DB::table('user_levels')->insert([
            'description' => 'FLUORITA',
            'experience_required' => 500
        ]);
        DB::table('user_levels')->insert([
            'description' => 'APATITO',
            'experience_required' => 1000
        ]);
        DB::table('user_levels')->insert([
            'description' => 'ORTOSA',
            'experience_required' => 1500
        ]);
        DB::table('user_levels')->insert([
            'description' => 'CUARZO',
            'experience_required' => 2000
        ]);
        DB::table('user_levels')->insert([
            'description' => 'TOPACIO',
            'experience_required' => 2500
        ]);
        DB::table('user_levels')->insert([
            'description' => 'CORINDÓN',
            'experience_required' => 3000
        ]);
        DB::table('user_levels')->insert([
            'description' => 'DIAMANTE',
            'experience_required' => 3500
        ]);
        DB::table('user_levels')->insert([
            'description' => 'RUBÍ',
            'experience_required' => 4000
        ]);
        DB::table('user_levels')->insert([
            'description' => 'ZAFIRO',
            'experience_required' => 4500
        ]);
    }
}
