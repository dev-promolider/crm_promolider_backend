<?php

namespace Database\Seeders;

use App\Models\Clas;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Module;
use App\Models\User;
use App\Models\UserExamHeader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Course::factory(50)->create();
        // Module::factory(50)->create();
        // Clas::factory(50)->create();


        // 1. Crear usuarios
        // User::factory(50)->create();

        // 2. Comprar cursos a los usuarios
        // $this->call(BuyCouseUserTest::class);

        // 3. Crear resultado de exámenes
        $this->call(CreateUserExams::class);



        // UserExamHeader::factory(50)->create();
        // factory para que usuarios compren 2 cursos los mismos usuarios 26 usuarios poblacion total
        // Exam::factory(50)->create();

    }
}
