<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $examType = new ExamType();
        $examType->description = "Examen de Curso";
        $examType->save();

        $examType1 = new ExamType();
        $examType1 ->description = "Examen de Módulo";
        $examType1 ->save();
        
        $examType2 = new ExamType();
        $examType2 ->description = "Examen de Clase";
        $examType2 ->save();
    }
}
