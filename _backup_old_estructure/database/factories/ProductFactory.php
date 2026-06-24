<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(3),
            'price' => $this->faker->randomFloat(2, 0, 400),
            'promotion_prince' => $this->faker->randomFloat(2, 0, 300),
            'commission' => rand(1, 180),
            'status' => $this->faker->randomElement(['0','1']),
        ];
    }
}
