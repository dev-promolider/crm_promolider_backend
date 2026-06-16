<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $courses = DB::table('courses')->pluck('id');
        // DB::table('modules')->insert([
        //     'id_courses' => $courses->random(),
        //     'name' => 'introduccion a python'
        // ]);
        DB::table('modules')->insert([
            'id_courses' => 1,
            'name' => 'Historia de Python',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 1,
            'name' => 'Introducción a Python',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 1,
            'name' => 'Principios de Python',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 10,
            'name' => 'Introducción',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 9,
            'name' => 'Introducción a Express',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 9,
            'name' => 'Instalación',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 8,
            'name' => 'Introducción',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 8,
            'name' => 'Tipos de datos',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 8,
            'name' => 'Bucles',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 7,
            'name' => 'Módulo 1',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 7,
            'name' => 'Módulo 2',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 6,
            'name' => 'Historia',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        DB::table('modules')->insert([
            'id_courses' => 6,
            'name' => 'Instalacion',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
