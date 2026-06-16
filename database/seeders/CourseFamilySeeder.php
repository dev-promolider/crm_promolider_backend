<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Family;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('course_families')->insert([
            'course_id' => 1,
            'family_id' => 1
        ]);
        DB::table('course_families')->insert([
            'course_id' => 2,
            'family_id' => 2
        ]);
        DB::table('course_families')->insert([
            'course_id' => 3,
            'family_id' => 1
        ]);
        DB::table('course_families')->insert([
            'course_id' => 4,
            'family_id' => 2
        ]);
        DB::table('course_families')->insert([
            'course_id' => 5,
            'family_id' => 1
        ]);
        DB::table('course_families')->insert([
            'course_id' => 6,
            'family_id' => 2
        ]);
        DB::table('course_families')->insert([
            'course_id' => 7,
            'family_id' => 3
        ]);
        DB::table('course_families')->insert([
            'course_id' => 8,
            'family_id' => 4
        ]);
        DB::table('course_families')->insert([
            'course_id' => 9,
            'family_id' => 5
        ]);
        DB::table('course_families')->insert([
            'course_id' => 10,
            'family_id' => 2
        ]);
    }
}
