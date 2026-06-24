<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_resources')->insert([
            'class_id' => 1,
            'resource_file' => 'courses/1/1/1/resources/python-cheat-sheet.pdf',
            'filename' => 'python-cheat-sheet.pdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 2,
            'resource_file' => 'courses/1/1/2/resources/electron.png',
            'filename' => 'electron.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 3,
            'resource_file' => 'courses/1/1/3/resources/840_560.jpg',
            'filename' => 'python-cheat-sheet.pdf',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 5,
            'resource_file' => 'courses/1/9/5/resources/imgen_.jpg',
            'filename' => 'imgen_.jpg',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 7,
            'resource_file' => 'courses/1/8/7/resources/ef0d92b3-74d6-4bec-bc4f-baa18dcf558e.png',
            'filename' => 'ef0d92b3-74d6-4bec-bc4f-baa18dcf558e.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 9,
            'resource_file' => 'courses/1/8/9/resources/840_560.png',
            'filename' => '840_560.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 11,
            'resource_file' => 'courses/1/7/11/resources/840_560.png',
            'filename' => '840_560.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 12,
            'resource_file' => 'courses/1/6/12/resources/840_560.png',
            'filename' => '840_560.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('class_resources')->insert([
            'class_id' => 13,
            'resource_file' => 'courses/1/6/13/resources/840_560.png',
            'filename' => '840_560.png',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);


    }
}
