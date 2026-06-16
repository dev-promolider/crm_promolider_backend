<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 1,
            'title' => 'Fundamentos de Python',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 2,
            'title' => 'Programación Orientada a Objetos',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);


        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 10,
            'title' => 'Examen final de ionic',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'lesson_id' => 4,
            'title' => 'Examen de clase 1 ionic',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'module_id' => 4,
            'title' => 'Examen de modulo 1 ionic',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 9,
            'title' => 'Examen final de Express',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'module_id' => 5,
            'title' => 'Examen de primer modulo express',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'lesson_id' => 5,
            'title' => 'Examen Clase Express',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 8,
            'title' => 'Examen final de Java',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'module_id' => 7,
            'title' => 'Examen de primer modulo Java',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 6,
            'title' => 'Examen final de Power BI',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'module_id' => 12,
            'title' => 'Examen de primer modulo Power BI',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'lesson_id' => 12,
            'title' => 'Examen Clase Power BI',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);

        DB::table('exam')->insert([
            'productor_id' => 1,
            'course_id' => 7,
            'title' => 'Examen final de Ruby',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'module_id' => 10,
            'title' => 'Examen de primer modulo Ruby',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
        DB::table('exam')->insert([
            'productor_id' => 1,
            'lesson_id' => 10,
            'title' => 'Examen Clase Ruby',
            'time' => 60,
            'max_score' => 100,
            'created_at' => $now,
            'min_passing_score' => 80,
            'status' => 0
        ]);
    }
}
