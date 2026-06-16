<?php

namespace Database\Seeders;

use App\Models\Classified;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassifiedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('classified')->insert([
            'user_id' => 1,
            'id_user_sponsor' => 1,
            'binary_sponsor' => "Admin",
            'position' => 1,
            'classification' => 1,
            'status' => 1,
            'authorized' => 1,
            'growth_bonus' => 0,
            'starting_bonus' => 0,
            'user_above' => 'top',
            'user_position_right' => null,
            'user_position_left' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // $cantUsers = User::all()->count();
        // for ($i = 2; $i <= $cantUsers; $i++){
        //     $user = User::find($i);
        //     $position = (bool)random_int(0, 1);
        //     Classified::factory([
        //         'user_id' => $user->id,
        //         'id_user_sponsor' => $user->id_referrer_sponsor,
        //         'status_position_left' => $position,
        //         'status_position_right' => !$position,
        //     ])->create();
        // }
    }
}
