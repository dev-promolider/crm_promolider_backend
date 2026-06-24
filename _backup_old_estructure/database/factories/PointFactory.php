<?php

namespace Database\Factories;

use App\Models\Point;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Point::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_membreship_id' => User::inRandomOrder()->first()->id,
            'reason' => $this->faker->word(),
             'user_points' => rand(1, 50),
            'leg' => $this->faker->randomElement(['left', 'right']),
        ];
    }
}
