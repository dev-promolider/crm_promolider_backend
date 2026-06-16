<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = array('Interbank','BCP', 'BBVA');
        //

        foreach ($banks as $bank){
            DB::table('bank')->insert([
                'name' => $bank,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
