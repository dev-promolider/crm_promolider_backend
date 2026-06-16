<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpansionBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $school_id = AccountType::where('account', 'School')->pluck('id')->first();
        $academy_id = AccountType::where('account', 'Academy')->pluck('id')->first();
        $university_id = AccountType::where('account', 'University')->pluck('id')->first();
        $data = [
            ['id_account_type' => $school_id, 'name' => '4-users', 'value' => 5],
            ['id_account_type' => $school_id, 'name' => '5-users', 'value' => 6],
            ['id_account_type' => $school_id, 'name' => '6-users', 'value' => 7],
            ['id_account_type' => $school_id, 'name' => '7-users', 'value' => 8],
            ['id_account_type' => $academy_id, 'name' => '4-users', 'value' => 5],
            ['id_account_type' => $academy_id, 'name' => '5-users', 'value' => 6],
            ['id_account_type' => $academy_id, 'name' => '6-users', 'value' => 7],
            ['id_account_type' => $academy_id, 'name' => '7-users', 'value' => 8],
            ['id_account_type' => $university_id, 'name' => '4-users', 'value' => 7],
            ['id_account_type' => $university_id, 'name' => '5-users', 'value' => 8],
            ['id_account_type' => $university_id, 'name' => '6-users', 'value' => 9],
            ['id_account_type' => $university_id, 'name' => '7-users', 'value' => 10],
        ];
        DB::table('expansion_bonus')->insert($data);
    }
}
