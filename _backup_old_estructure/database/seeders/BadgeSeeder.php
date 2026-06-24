<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('badges')->insert([
            'name' => 'Aprobar curso 1',
            'description' => 'Logro aprobar curso 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'passed_course_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Aprobar curso 2',
            'description' => 'Logro aprobar curso 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'pased_course_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Aprobar curso 3',
            'description' => 'Logro aprobar curso 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'pased_course_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Aprobar examen 1',
            'description' => 'Logro aprobar examen 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'passe_exam_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Aprobar examen 2',
            'description' => 'Logro aprobar examen 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'passed_exam_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Aprobar examen 3',
            'description' => 'Logro aprobar examen 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'passed_exam_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Comprador de cursos 1',
            'description' => 'Logro de comprador de cursos 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'course_buyer_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Comprador de cursos 2',
            'description' => 'Logro de comprador de cursos 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'course_buyer_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Comprador de cursos 3',
            'description' => 'Logro de comprador de cursos 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'course_buyer_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Creador de cursos 1',
            'description' => 'Logro de creador de cursos 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'course_creator_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Creador de cursos 2',
            'description' => 'Logro de creador de cursos 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'course_creator_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Creador de cursos 3',
            'description' => 'Logro de creador de cursos 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'course_creator_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Pregunta diaria 1',
            'description' => 'Logro de pregunta diaria 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'daily_quiz_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Pregunta diaria 2',
            'description' => 'Logro de pregunta diaria 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'daily_quiz_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Pregunta diaria 3',
            'description' => 'Logro de pregunta diaria 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'daily_quiz_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Recolector de suscriptores 1',
            'description' => 'Logro de recolector de suscriptores 1',
            'level' => 1,
            'condition' => 1,
            'icon' => 'sub_collector_one-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Recolector de suscriptores 2',
            'description' => 'Logro de recolector de suscriptores 2',
            'level' => 2,
            'condition' => 10,
            'icon' => 'sub_collector_two-min.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Recolector de suscriptores 3',
            'description' => 'Logro de recolector de suscriptores 3',
            'level' => 3,
            'condition' => 30,
            'icon' => 'sub_collector_three-min.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Invitar usuarios 1',
            'description' => 'Logro invitar usuarios 1',
            'level' => 1, // nivel principiante
            'condition' => 1, // n de cursos creados
            'icon' => 'invite_user_one.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Invitar usuarios 2',
            'description' => 'Logro invitar usuarios 2',
            'level' => 2, // nivel principiante
            'condition' => 10, // n de cursos creados
            'icon' => 'invite_user_two.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Invitar usuarios 3',
            'description' => 'Logro invitar usuarios 3',
            'level' => 3, // nivel principiante
            'condition' => 30, // n de cursos creados
            'icon' => 'invite_user_three.png',
        ]);

        DB::table('badges')->insert([
            'name' => 'Membresia basica',
            'description' => 'Logro membresia basica',
            'level' => 1, // nivel principiante
            'condition' => 1, // n de cursos creados
            'icon' => 'basic.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Membresia school',
            'description' => 'Logro membresia school',
            'level' => 1, // nivel principiante
            'condition' => 1, // n de cursos creados
            'icon' => 'school.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Membresia academy',
            'description' => 'Logro membresia academy',
            'level' => 1, // nivel principiante
            'condition' => 1, // n de cursos creados
            'icon' => 'academy.png',
        ]);
        DB::table('badges')->insert([
            'name' => 'Membresia university',
            'description' => 'Logro membresia university',
            'level' => 1, // nivel principiante
            'condition' => 1, // n de cursos creados
            'icon' => 'university.png',
        ]);
    }
}
