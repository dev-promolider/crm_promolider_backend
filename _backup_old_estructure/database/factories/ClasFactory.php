<?php

namespace Database\Factories;

use App\Models\Clas;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClasFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Clas::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $url = ['https://static.videezy.com/system/resources/previews/000/033/826/original/pattaya-aerial-view30.mp4', 'https://media.istockphoto.com/videos/aerial-view-panoramic-seascape-swimming-pool-on-the-roof-of-the-on-video-id992488002'];

        return [
            'id_modules' => Module::inRandomOrder()->first()->id,
            'name' => $this->faker->streetName(),
            'time' => $this->faker->time($format = 'H:i:s', $max = '6:00:00'),
            'url'  => $url[array_rand($url)],
            'description' => $this->faker->text($maxNbChars = 200),
        ];
    }
}
