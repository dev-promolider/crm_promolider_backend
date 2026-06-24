<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('course_level')->insert([
            'description' => 'Basico',
        ]);

        DB::table('course_level')->insert([
            'description' => 'Intermedio',
        ]);

        DB::table('course_level')->insert([
            'description' => 'Avanzado',
        ]);
    }
}
