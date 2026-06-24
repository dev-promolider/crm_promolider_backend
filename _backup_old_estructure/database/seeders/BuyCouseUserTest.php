<?php

namespace Database\Seeders;

use App\Models\GrowthBonus;
use App\Models\StartingBonus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyCouseUserTest extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // curso 1 y 2

        $startId = 4;

        for ($i = 1; $i <= 26; $i++) {
            $user = $startId + $i;
            $purchasedCourse = DB::table('purchased_courses')->insert([
                'classes_status' => '[[1, "NOT SEEN"]]',
                'user_id' => $user,
                'course_id' => 1,
                'last_class_reprod' => 1,
                'completed_course' => 0,
                'display_time' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $startId2 = 4;

        for ($i = 1; $i <= 26; $i++) {
            $user = $startId2 + $i;
            $purchasedCourse = DB::table('purchased_courses')->insert([
                'classes_status' => '[[2, "NOT SEEN"], [3, "NOT SEEN"], [4, "NOT SEEN"], [5, "NOT SEEN"], [6, "NOT SEEN"], [7, "NOT SEEN"], [8, "NOT SEEN"], [9, "NOT SEEN"]]',
                'user_id' => $user,
                'course_id' => 2,
                'last_class_reprod' => 1,
                'completed_course' => 0,
                'display_time' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
