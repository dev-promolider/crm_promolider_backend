<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['description' => 'default_avatar', 'value' => 'avatar1.png'],
            ['description' => 'daily_question', 'value' => '10'],
            ['description' => 'achievement', 'value' => '10'],
            ['description' => 'badges_level_one', 'value' => '10'],
            ['description' => 'badges_level_two', 'value' => '20'],
            ['description' => 'badges_level_three', 'value' => '30'],  
            ['description' => 'currency_value', 'value' => '0.29'],
            ['description' => 'batch', 'value' => '1'],
            ['description' => 'last_expansion_deliver', 'value' => now()],
         ];

         DB::table('options')->insert($data);


        // DB::table('options')->insert([
        //     'description' => 'avatar1',
        //     'value' => 'introduccion a python'
        // ]);
    
       
    }
}
