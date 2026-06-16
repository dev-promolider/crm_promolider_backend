<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user1 = User::factory([
        //     'username' => 'admin',
        //     'name' => 'Administrator',
        //     'last_name' => 'Promolider',
        //     'email' => 'admin@promolider.test',
        //     'id_referrer_sponsor' => 0,
        //     'request' => 3,
        //     'expiration_date' => strtotime('+10 years'),
        //     'id_account_type' => 1,
        //     'created_at' => strtotime('-1 years'),
        //     'user_type' => 0,
        //     'photo' => 'images/avatar-s-11.png'
        // ])->create();

        DB::table('users')->insert([
            'username' => 'admin',
            'password' => '$2y$10$bffVuE/PLXCf3wGqxpdXkuGVM76je.Spo2/6MG36zyKsMPzClDjsq',
            'email' => 'dsanchez@promolider.org',
            'name' => 'admin',
            'last_name' => 'admin',
            'date_birth' => '1990-01-01',
            'phone' => '987654321',
            'id_country' => 1,
            'id_document_type' => 1,
            'nro_document' => '12345678',
            'id_account_type' => 1,
            'id_referrer_sponsor' => 0,
            'request' => 2,
            'expiration_date' => strtotime('+10 years'),
            // 'created_at' => strtotime('-1 years'),
            'created_at' =>  '2021-12-31 12:00:00',
            'user_type' => 0,
            'photo' => 'images/avatar-s-11.png',
            'biography' => 'Web dev',
            'city' => ' Lima'
        ]);


        // DB::table('users')->insert([
        //     'username' => 'diego',
        //     'password' => '$2y$10$bffVuE/PLXCf3wGqxpdXkuGVM76je.Spo2/6MG36zyKsMPzClDjsq',
        //     'email' => 'diegopalominosa@gmail.com',
        //     'status_user' => '1',
        //     'name' => 'Diego',
        //     'last_name' => 'Sanchez',
        //     'date_birth' => '2001-05-23',
        //     'phone' => '939978482',
        //     'id_country' => 1,
        //     'id_document_type' => '1',
        //     'nro_document' => '76093647',
        //     'id_account_type' => '5',
        //     'id_referrer_sponsor' => '1',
        //     'request' => '2',
        //     'expiration_date' => '2022-07-23 11:06:08',
        //     'position' => '1',
        //     'created_at' => Carbon::now(),
        //     'photo' => 'images/avatar2.png',
        //     'biography' => 'Web dev',
        //     'status_preference' => 1,
        //     'daily_quizz_status' => 0,
        //     'city' => ' Lima'
        // ]);



        Artisan::call('passport:client --name=<client-name> --no-interaction --personal');
    }
}
