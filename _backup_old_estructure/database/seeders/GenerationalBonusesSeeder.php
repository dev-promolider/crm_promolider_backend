<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenerationalBonusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('generational_bonuses')->insert([
            [
                'range_name' => 'Mentor',
                'g_1' => 5.00,
                'g_2' => 0.00,
                'g_3' => 0.00,
                'g_4' => 0.00,
                'g_5' => 0.00,
                'g_6' => 0.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Entrenador Académico',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 0.00,
                'g_4' => 0.00,
                'g_5' => 0.00,
                'g_6' => 0.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Coach Acreditado',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 0.00,
                'g_5' => 0.00,
                'g_6' => 0.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Master Acreditado',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 0.00,
                'g_6' => 0.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Sub Director',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 2.00,
                'g_6' => 0.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Director',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 2.00,
                'g_6' => 1.00,
                'g_7' => 0.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Decano',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 3.00,
                'g_6' => 2.00,
                'g_7' => 1.00,
                'g_8' => 0.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Vice Rector',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 3.00,
                'g_6' => 3.00,
                'g_7' => 2.00,
                'g_8' => 1.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Rector',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 3.00,
                'g_6' => 3.00,
                'g_7' => 2.00,
                'g_8' => 2.00,
                'created_at' => null,
                'updated_at' => null,
            ],
            [
                'range_name' => 'Rector Presidente',
                'g_1' => 5.00,
                'g_2' => 5.00,
                'g_3' => 5.00,
                'g_4' => 3.00,
                'g_5' => 3.00,
                'g_6' => 3.00,
                'g_7' => 2.00,
                'g_8' => 2.00,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
