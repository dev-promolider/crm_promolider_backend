<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserConfiguration;
class UserConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserConfiguration::updateOrCreate(
            ['id' => 1, 'user_id' => 1, 'configuration_id' => 1],
            [
                'value' => 1,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['id' => 2, 'user_id' => 1, 'configuration_id' => 2],
            [
                'value' => 'img_no_almacenada.jpg',
            ]
        );
        UserConfiguration::updateOrCreate(
            ['id' => 3, 'user_id' => 1, 'configuration_id' => 3],
            [
                'value' => 10,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['id' => 4, 'user_id' => 1, 'configuration_id' => 4],
            [
                'value' => 10,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['id' => 5, 'user_id' => 1, 'configuration_id' => 5],
            [
                'value' => 10,
            ]
        );
        UserConfiguration::updateOrCreate(
            ['id' => 6, 'user_id' => 1, 'configuration_id' => 6],
            [
                'value' => 10,
            ]
        );
    }
}
