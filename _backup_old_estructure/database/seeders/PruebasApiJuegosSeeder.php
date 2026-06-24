<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PruebasApiJuegosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['nombre' => 'Jose'],
            ['nombre' => 'Alberto'],
            ['nombre' => 'Kiara'],
            ['nombre' => 'Anthuanet'],
            ['nombre' => 'Maria'],
            ['nombre' => 'Federico'],
            ['nombre' => 'Italo'],
            ['nombre' => 'Estela'],
        ];
        DB::table('pruebas_api_juegos')->insert($data);
    }
}
