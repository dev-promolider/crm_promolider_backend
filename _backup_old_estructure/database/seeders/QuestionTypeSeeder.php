<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('question_type')->insert([
            'name' => 'Selección Simple',
        ]);
        DB::table('question_type')->insert([
            'name' => 'Selección Múltiple',
        ]);
        DB::table('question_type')->insert([
            'name' => 'Selección Binaria',
        ]);
        DB::table('question_type')->insert([
            'name' => 'Pregunta abierta',
        ]);
    }
}
