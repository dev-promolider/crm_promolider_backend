<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class')->insert([
            'id_modules' => 1,
            'name' => 'Clase 1 de python',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 2,
            'name' => 'Clase 2 de python',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 3,
            'name' => 'Clase 3 de python',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 4,
            'name' => 'Clase 1',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 5,
            'name' => 'Historia',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 6,
            'name' => '¿Que necesitamos?',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 7,
            'name' => 'Variables y Constantes',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 8,
            'name' => 'Enteros y Flotantes',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 9,
            'name' => 'While, Do while, While Do, Foreach, For',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 10,
            'name' => 'Clase 1',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 11,
            'name' => 'Clase 2',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 12,
            'name' => 'Historia de Power BI',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class')->insert([
            'id_modules' => 13,
            'name' => '¿Cómo instalar Power BI?',
            'url' => '/class/example',
            'status' => 2,
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dictum eros tincidunt risus fermentum porttitor. Suspendisse rutrum sodales arcu vel eleifend. Nulla efficitur odio at erat auctor, ut fringilla massa dignissim',
            'time' => '00:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
