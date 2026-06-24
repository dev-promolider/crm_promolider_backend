<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\AccountTypePointsMoney;
use Illuminate\Database\Seeder;

class AccountTypePoinstMoneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccountTypePointsMoney::create(['account_type_id' => 1, 'points' => 0, 'money' => 0,]); // Admin
        AccountTypePointsMoney::create(['account_type_id' => 5, 'points' => 0, 'money' => 5,]); // Basic
        AccountTypePointsMoney::create(['account_type_id' => 2, 'points' => 29, 'money' => 35,]); // School
        AccountTypePointsMoney::create(['account_type_id' => 3, 'points' => 89, 'money' => 105,]); // Academy
        AccountTypePointsMoney::create(['account_type_id' => 4, 'points' => 234, 'money' => 280,]); // University
        AccountTypePointsMoney::create(['account_type_id' => 6, 'points' => 5, 'money' => 5,]); // Guest
    }
}
