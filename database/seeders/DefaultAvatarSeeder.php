<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultAvatarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['link' => 'avatar1.png'],
            ['link' => 'avatar2.png'],
            ['link' => 'avatar3.png'],                  
         ];

         DB::table('default_avatars')->insert($data);
    }
}
