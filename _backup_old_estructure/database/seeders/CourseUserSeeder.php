<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseUser;

class CourseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CourseUser::factory(50)->create();
    }
}
