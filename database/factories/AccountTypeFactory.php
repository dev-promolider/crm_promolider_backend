<?php

namespace Database\Factories;

use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account' => $this->faker->word(),
            'price' => $this->faker->randomFloat(5, 10,999.99), // 48.8932
            'status' => '0',
            'iva'=> $this->faker->randomFloat(5, 10,999.99),
            'disc_purchases_course'=> $this->faker->randomFloat(5, 10,999.99),
            'pay_in_binary'=> $this->faker->randomFloat(5, 10,999.99),
            'profit_on_purchases'=> $this->faker->randomFloat(5, 10,999.99),
            'profit_on_purchases_2'=> $this->faker->randomFloat(5, 10,999.99),
            'comission'=> $this->faker->randomFloat(5, 10,999.99),
            'fast_cash_bonus' => 18,
        ];
    }
}
