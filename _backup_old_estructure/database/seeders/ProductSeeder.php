<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Product::factory()->count(15)->create();

        DB::table('product')->insert([
            'name' => 'opc',
            'descripcion' => 'actualización de 30 dias',
            'price' => 51,
            'promotion_prince' => 0,
            'commission' => 0,
            'status' => '1',
            'points' => 15,
        ]);
    }
}
