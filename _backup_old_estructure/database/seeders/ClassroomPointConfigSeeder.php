<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomPointConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('classroom_point_configs')->insert([
            'passed_course' => 10,
            'daily_question' => 10,
            'achievement' => 10,
        ]);
    }
}
