<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class RolesUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::find(1);
        $user1->assignRole('Admin');
        // $user2 = User::find(2);
        // $user2->assignRole('Producer');
        // $user3 = User::find(3);
        // $user3->assignRole('Producer');
        // $user4 = User::find(4);
        // $user4->assignRole('Producer');
        // $user5 = User::find(5);
        // $user5->assignRole('Producer');
        // $user6 = User::find(6);
        // $user6->assignRole('Producer');
    }
}
