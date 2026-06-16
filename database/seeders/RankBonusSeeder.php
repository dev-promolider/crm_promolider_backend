<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rank_bonus')->insert([
            'name' => 'Aprendiz',
            'vol_min' => 0,
            'pack_max' => 0,
            'active_direct' => 0,
            'max_pay' => 200,
            'monthly_bonus' => 0,
            'extra_bonus' => 0,
            'limit_generation' => 0,
            'icon' => 'images/ranks/rango mentor.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Mentor',
            'vol_min' => 70,
            'pack_max' => 0,
            'active_direct' => 2,
            'max_pay' => 500,
            'monthly_bonus' => 0,
            'extra_bonus' => 0,
            'limit_generation' => 1,
            'icon' => 'images/ranks/rango mentor.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Entrenador Académico',
            'vol_min' => 840,
            'pack_max' => 1,
            'active_direct' => 2,
            'max_pay' => 1000,
            'monthly_bonus' => 0,
            'extra_bonus' => 0,
            'limit_generation' => 2,
            'icon' => 'images/ranks/rango entrenador academico.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Coach Acreditado',
            'vol_min' => 2800,
            'pack_max' => 2,
            'active_direct' => 3,
            'max_pay' => 1500,
            'monthly_bonus' => 0,
            'extra_bonus' => 0,
            'limit_generation' => 3,
            'icon' => 'images/ranks/rango coach acreditado.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Master Acreditado',
            'vol_min' => 9800,
            'pack_max' => 5,
            'active_direct' => 5,
            'max_pay' => 3500,
            'monthly_bonus' => 0,
            'extra_bonus' => 0,
            'limit_generation' => 4,
            'icon' => 'images/ranks/rango master acreditado.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Sub Director',
            'vol_min' => 20650,
            'pack_max' => 12,
            'active_direct' => 6,
            'max_pay' => 5000,
            'monthly_bonus' => 1000,
            'extra_bonus' => 0,
            'limit_generation' => 5,
            'icon' => 'images/ranks/rango sub director.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Director',
            'vol_min' => 63000,
            'pack_max' => 30,
            'active_direct' => 6,
            'max_pay' => 7500,
            'monthly_bonus' => 2000,
            'extra_bonus' => 0,
            'limit_generation' => 6,
            'icon' => 'images/ranks/rango director.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Decano',
            'vol_min' => 126000,
            'pack_max' => 80,
            'active_direct' => 6,
            'max_pay' => 10000,
            'monthly_bonus' => 4000,
            'extra_bonus' => 0,
            'limit_generation' => 7,
            'icon' => 'images/ranks/rango decano.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Vice Rector',
            'vol_min' => 259000,
            'pack_max' => 150,
            'active_direct' => 7,
            'max_pay' => 15000,
            'monthly_bonus' => 7000,
            'extra_bonus' => 0,
            'limit_generation' => 8,
            'icon' => 'images/ranks/rango vicerrector.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Rector',
            'vol_min' => 777000,
            'pack_max' => 420,
            'active_direct' => 7,
            'max_pay' => 20000,
            'monthly_bonus' => 10000,
            'extra_bonus' => 0,
            'limit_generation' => 8,
            'icon' => 'images/ranks/rango rector.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Rector Presidente',
            'vol_min' => 2087000,
            'pack_max' => 980,
            'active_direct' => 7,
            'max_pay' => 30000,
            'monthly_bonus' => 15000,
            'extra_bonus' => 0,
            'limit_generation' => 8,
            'icon' => 'images/ranks/rango rector presidente.png'
        ]);

        DB::table('rank_bonus')->insert([
            'name' => 'Rector Presidente Crown',
            'vol_min' => 5507000,
            'pack_max' => 5000,
            'active_direct' => 8,
            'max_pay' => 50000,
            'monthly_bonus' => 20000,
            'extra_bonus' => 0,
            'limit_generation' => 8,
            'icon' => 'images/ranks/rector presidente crown.png'
        ]);
    }
}
