<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = User::all()->count();
        for ($i=1; $i <= $count; $i++) { 
            Wallet::factory()->create(['user_id' => $i]);
        }
    }
}
