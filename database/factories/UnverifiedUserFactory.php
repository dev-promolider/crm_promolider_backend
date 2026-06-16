<?php

namespace Database\Factories;

use App\Models\UnverifiedUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnverifiedUserFactory extends Factory
{
    protected $model = UnverifiedUser::class;

    public function definition()
    {
        return [
            'username'         => $this->faker->unique()->userName(),
            'password'         => bcrypt('secret'),
            'openpay_order_id' => 'openpay_' . $this->faker->uuid(),
            'data'             => json_encode(['preregistro_id' => null]),
        ];
    }
}
