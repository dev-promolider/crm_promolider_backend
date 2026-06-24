<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
       DB::table('account_type')->insert([
            'account' => "Admin",
            'price' => 100000.00,
            'status' => 0,
            'iva' => 0,
            'fast_cash_bonus' => 0,
            'disc_purchases_course' => 0,
            'disc_purchases_certificates' => 0,
            'pay_in_binary' => 0,
            'productor_bonus' => 0,
            'course_selling_bonus' => 0,
            'comission' =>  50,
            'enrollment_duration' => 12
        ]);

        DB::table('account_type')->insert([
            'account' => "School",
            'price' => 100.00,
            'status' => 1,
            'iva' => 18,
            'fast_cash_bonus' => 18,
            'disc_purchases_course' => 10,
            'disc_purchases_certificates' => 15,
            'pay_in_binary' => 20,
            'productor_bonus' => 15,
            'course_selling_bonus' => 20,
            'comission' =>  10,
            'enrollment_duration' => 12
        ]);

        DB::table('account_type')->insert([
            'account' => "Academy",
            'price' => 305.00,
            'status' => 1,
            'iva' => 18,
            'fast_cash_bonus' => 18,
            'disc_purchases_course' => 15,
            'disc_purchases_certificates' => 20,
            'pay_in_binary' => 30,
            'productor_bonus' => 20,
            'course_selling_bonus' => 20,
            'comission' =>  20,
            'enrollment_duration' => 12
        ]);

        DB::table('account_type')->insert([
            'account' => "University",
            'price' => 805.00,
            'status' => 1,
            'iva' => 18,
            'fast_cash_bonus' => 20,
            'disc_purchases_course' => 20,
            'disc_purchases_certificates' => 30,
            'pay_in_binary' => 50,
            'productor_bonus' => 30,
            'course_selling_bonus' => 25,
            'comission' =>  30,
            'enrollment_duration' => 12
        ]);

        DB::table('account_type')->insert([
            'account' => "Basic",
            'price' => 0.00,
            'status' => 1,
            'iva' => 18,
            'fast_cash_bonus' => 0,
            'disc_purchases_course' => 0,
            'disc_purchases_certificates' => 0,
            'pay_in_binary' => 0,
            'productor_bonus' => 0,
            'course_selling_bonus' => 0,
            'comission' =>  2,
            'enrollment_duration' => 12
        ]);
        DB::table('account_type')->insert([
            'account' => "Guest",
            'price' => 16.94,
            'status' => 1,
            'iva' => 18,
            'fast_cash_bonus' => 0,
            'disc_purchases_course' => 0,
            'disc_purchases_certificates' => 0,
            'pay_in_binary' => 0,
            'productor_bonus' => 0,
            'course_selling_bonus' => 0,
            'comission' =>  2,
            'enrollment_duration' => 12
        ]);
    }
}
