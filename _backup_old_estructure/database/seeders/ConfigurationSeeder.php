<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuration::updateOrCreate(
            ['id' => 1 ],
            [
                'option' => 'certificate_template',
            ]
        );
        Configuration::updateOrCreate(
            ['id' => 2 ],
            [
                'option' => 'signature',
            ]
        );
        Configuration::updateOrCreate(
            ['id' => 3 ],
            [
                'option' => 'points_exam_course',
            ]
        );
        Configuration::updateOrCreate(
            ['id' => 4 ],
            [
                'option' => 'exam_timer',
            ]
        );
        Configuration::updateOrCreate(
            ['id' => 5 ],
            [
                'option' => 'dynamics_1',
            ]
        );
        Configuration::updateOrCreate(
            ['id' => 6 ],
            [
                'option' => 'dynamics_2',
            ]
        );
    }
}
