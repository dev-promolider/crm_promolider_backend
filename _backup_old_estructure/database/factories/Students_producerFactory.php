<?php

namespace Database\Factories;

use App\Models\Students_producer;
use Illuminate\Database\Eloquent\Factories\Factory;

class Students_producerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Students_producer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_students' => $this->faker->numberBetween(1,5),
            'id_producers' => $this->faker->numberBetween(1,5),
        ];
    }
}
