<?php

namespace Database\Factories;

use App\Models\PreregistroLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class PreregistroLinkFactory extends Factory
{
    protected $model = PreregistroLink::class;

    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'lado'     => 'izquierda',
            'landing'  => 'claro',
        ];
    }
}
