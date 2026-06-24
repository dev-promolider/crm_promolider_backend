<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

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
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'operation_number' => rand(1500, 6868),
            'id_payment_method' => PaymentMethod::inRandomOrder()->first()->id,
        ];
    }
}
