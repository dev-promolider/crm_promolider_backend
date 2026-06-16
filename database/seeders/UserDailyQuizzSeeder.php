<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserDailyQuizzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_daily_quizzs')->insert([
            'id_user' => 1,
            'passed_quizz' => 0
        ]);
    }
}
