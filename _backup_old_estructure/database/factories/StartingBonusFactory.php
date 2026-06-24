<?php

namespace Database\Factories;

use App\Models\StartingBonus;
use Illuminate\Database\Eloquent\Factories\Factory;

class StartingBonusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StartingBonus::class;

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
