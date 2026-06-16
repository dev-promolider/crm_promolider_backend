<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserClassroomPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_classroom_points')->insert([
            'id_user' => 1,
            'total_points' => 0
        ]);

        // DB::table('classroom_point_details')->insert([
        //     'id_user_classroom_points' => 1,
        //     'increment_points' => 30,
        //     'description' => 'Nueva venta'
        // ]);
        // DB::table('classroom_point_details')->insert([
        //     'id_user_classroom_points' => 1,
        //     'increment_points' => 70,
        //     'description' => 'Examen de Python'
        // ]);
        // DB::table('classroom_point_details')->insert([
        //     'id_user_classroom_points' => 1,
        //     'increment_points' => 100,
        //     'description' => 'Compra certificado'
        // ]);
    }
}
