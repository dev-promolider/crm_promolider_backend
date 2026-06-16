<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $messages = array('It is a long established fact that a reader will  layout.normal distribution of letters, as opposed to using','be distracted by the readable content of a page when looking at its',
            'The point of using Lorem Ipsum is that it has a more-or-less');
        //

        foreach ($messages as $message) {
            DB::table('advertisements')->insert([
                'message' => $message,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
