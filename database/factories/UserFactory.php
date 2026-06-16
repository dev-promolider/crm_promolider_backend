<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        static $password;

        $username = $this->faker->unique()->username();
        $nro_document = $this->faker->unique()->numberBetween(10000000, 99999999);
        $city = $this->faker->city();
        $date_birth = $this->faker->dateTimeBetween('-30 years', '-18 years')->format('Y-m-d');
        $id_document_type = DB::table('document_type')->inRandomOrder()->first()->id;
        $phone = $this->faker->unique()->numberBetween(900000000, 999999999);
        $id_country = DB::table('country')->inRandomOrder()->first()->id;
        $id_account_type = DB::table('account_type')->inRandomOrder()->first()->id;
        $id_referrer_sponsor = DB::table('users')->inRandomOrder()->first()->id;

        return [
            'username' => $username,
            'password' => $password ?: $password = bcrypt('secret'),
            'email' => $this->faker->unique()->email(),
            'name' => $this->faker->name(),
            'last_name' => $this->faker->lastName(),
            'date_birth' => $date_birth,
            'phone' => $phone,
            'id_country' => $id_country,
            'city' => $city,
            'id_document_type' => $id_document_type,
            'nro_document' => $nro_document,
            'id_account_type' => $id_account_type,
            'request' => 1,
            'id_referrer_sponsor' => $id_referrer_sponsor,
            'position' => "0",
            'user_type' => 2,
            'photo' => "https://i.pravatar.cc/150?u={$username}",
            'biography' => $this->faker->sentence(12)
        ];
    }
}
