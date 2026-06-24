<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BonusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bonus_type')->insert([
            'description' => 'Bono de efectivo rápido',
        ]);
        DB::table('bonus_type')->insert([
            'description' => 'Bono por compra de curso',
        ]);
        DB::table('bonus_type')->insert([
            'description' => 'Bono de productor',
        ]);
        DB::table('bonus_type')->insert([
            'description' => 'Bono por corte binario',
        ]);
        DB::table('bonus_type')->insert([
            'description' => 'Bono de rangos',
        ]);

        DB::table('bonus_type')->insert([
            'description' => 'Bono de expansión',
        ]);
        // DB::table('bonus_type')->insert([
        //     'description' => 1,
        // ]);
        // DB::table('bonus_type')->insert([
        //     'description' => 1,
        // ]);
        // DB::table('bonus_type')->insert([
        //     'description' => 1,
        // ]);
    }
}
