<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('badge_detail')->insert([
            'user_id' => 1,
            'badge_id' => 1,
        ]);
        DB::table('badge_detail')->insert([
            'user_id' => 1,
            'badge_id' => 2,
        ]);
        DB::table('badge_detail')->insert([
            'user_id' => 1,
            'badge_id' => 4,
        ]);
        DB::table('badge_detail')->insert([
            'user_id' => 1,
            'badge_id' => 5,
        ]);

    }
}
