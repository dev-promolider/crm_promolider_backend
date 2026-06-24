<?php

namespace Database\Factories;

use App\Models\Classified;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassifiedFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Classified::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'id_user_sponsor' => User::inRandomOrder()->first()->id,
            'binary_sponsor' => $this->faker->word,
            'position' => rand(1, 2),
            'classification' => $this->faker->randomElement([16,26]),
            'status' => rand(0, 1),
            'authorized' => rand(0, 1),
            'status_position_left' => rand(0, 1),
            'status_position_right' => rand(0, 1),
            'growth_bonus' => $this->faker->randomFloat(2, 0, 300),
            'starting_bonus' => $this->faker->randomFloat(2, 0, 300),
        ];
    }
}
