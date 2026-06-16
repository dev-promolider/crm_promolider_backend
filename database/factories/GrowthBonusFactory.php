<?php

namespace Database\Factories;

use App\Models\GrowthBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrowthBonusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GrowthBonus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'price' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
