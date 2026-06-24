<?php

namespace Database\Seeders;

use App\Models\GrowthBonus;
use App\Models\StartingBonus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateUserExams extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        // Crear registros de los 26 usuarios para cada examen de curso de ambos cursos

        $startId = 4;
        for ($i = 1; $i <= 26; $i++) {
            $probability  = rand(1, 10);

            if ($probability > 3) {
                $rate = $faker->numberBetween(80, 100); // 70% aprobados - pre test
                $condition = 'Aproved';
            } else {
                $rate = $faker->numberBetween(0, 79);
                $condition = 'Disaproved';
            }

            $created_at = $faker->dateTimeBetween($startDate = '-40 days', $endDate = '-28 days', $timezone = null);

            $user = $startId + $i;
            $exam = DB::table('user_exam_header')->insert([
                'rate' =>  $rate,
                'condition' => $condition,
                'created_at' => $created_at,
                'exam_id' => 1,
                'productor_id' => 3,
                'status' => 1,
                'user_id' => $user,
            ]);

            if ($condition == 'Aproved') {
                $badge = DB::table('badge_detail')->insert([
                    'user_id' => $user,
                    'badge_id' => 4,
                    'created_at' => $created_at,
                ]);
            }
        }

        $startId2 = 4;
        for ($i = 1; $i <= 26; $i++) {
            $probability  = rand(1, 10);

            if ($probability > 1) {
                $rate = $faker->numberBetween(80, 100); // 90% aprobados - pre test
                $condition = 'Aproved';
            } else {
                $rate = $faker->numberBetween(0, 79);
                $condition = 'Disaproved';
            }

            $created_at = $faker->dateTimeBetween($startDate = '-1 day', $endDate = 'now', $timezone = null);

            $user = $startId2 + $i;
            $exam = DB::table('user_exam_header')->insert([
                'rate' =>  $rate,
                'condition' => $condition,
                'created_at' => $created_at,
                'exam_id' => 2,
                'productor_id' => 3,
                'status' => 1,
                'user_id' => $user,
            ]);

            if ($condition == 'Aproved') {
                $badge = DB::table('badge_detail')->insert([
                    'user_id' => $user,
                    'badge_id' => 4,
                    'created_at' => $created_at,
                ]);
            }
        }
    }
}
