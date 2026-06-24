<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchasedCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = DB::table('users')->pluck('id');
        $courses = DB::table('courses')->pluck('id');

        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
        DB::table('purchased_courses')->insert([
            'classes_status' => '[[1, "SEEN"], [2, "NOT SEEN"], [3, "SEEN"], [4, "SEEN"], [5, "SEEN"], [6, "SEEN"], [7, "NOT SEEN"]]',
            'course_id' => $courses->random(),
            'user_id' =>  $users->random(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'last_class_reprod' => 3,
            'completed_course' => 1,
            'completed_date' => '2022-03-05'
        ]);
    }
}
