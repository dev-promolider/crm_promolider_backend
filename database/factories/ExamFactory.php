<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class ExamFactory extends Factory
{
    // pre y post test serán de 2 exámenes de curso
    // usuarios matriculados son el total de usuarios por cada curso 
    // un porcentaje de ellos serán los que pasen el examen


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $course_id = 0;
        // $module_id = 0;
        // $lesson_id = 0;

        $productor_id = DB::table('users')->inRandomOrder()->first()->id;
        $number = $this->faker->numberBetween(1, 3);
        // if ($number == 1) {
        //     $course_id = DB::table('courses')->inRandomOrder()->first()->id;
        // } else if ($number == 2) {
        //     $module_id = DB::table('modules')->inRandomOrder()->first()->id;
        // } else {
        //     $lesson_id = DB::table('class')->inRandomOrder()->first()->id;
        // }

        $title = $this->faker->sentence(5);
        $time = 0;
        $status = 1;
        $max_score = 100;
        $min_score = 80;

        return [
            'productor_id' => $productor_id,
            'course_id' => $course_id == 0 ? null : $course_id,
            'title' => $title,
            'time' => $time,
            'status' => $status,
            'max_score' => $max_score,
            'min_passing_score' => $min_score,
        ];
    }
}
