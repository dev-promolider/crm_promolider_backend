<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "course" => 1,
                "payment" => 1,
                "price" => 20,
                "desc" => 5.1
            ],
            [
                "course" => 2,
                "payment" => 2,
                "price" => 20,
                "desc" => 5.1
            ],
            [
                "course" => 3,
                "payment" => 3,
                "price" => 20,
                "desc" => 5.1
            ],
        ];
        foreach ($data as $val) {
            DB::table('courses_payments')->insert([
                'course_id' => $val['course'],
                'payment_id' => $val['payment'],
                "desc" => $val['desc'],
                "price" => $val['price']
            ]);
        }
    }
}
