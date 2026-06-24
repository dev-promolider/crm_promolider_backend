<?php

namespace Database\Seeders;

use App\Models\GrowthBonus;
use App\Models\StartingBonus;
use Illuminate\Database\Seeder;

class BonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GrowthBonus::factory()->times(5)->create();
        StartingBonus::factory()->times(5)->create();
    }
}
