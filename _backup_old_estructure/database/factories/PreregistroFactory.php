<?php

namespace Database\Factories;

use App\Models\Preregistro;
use Illuminate\Database\Eloquent\Factories\Factory;

class PreregistroFactory extends Factory
{
    protected $model = Preregistro::class;

    public function definition()
    {
        return [
            'nombres'           => $this->faker->firstName(),
            'apellidos'         => $this->faker->lastName(),
            'correo'            => $this->faker->unique()->safeEmail(),
            'whatsapp'          => $this->faker->phoneNumber(),
            'referrer_username' => 'testuser',
            'lado'              => 'izquierda',
        ];
    }
}
