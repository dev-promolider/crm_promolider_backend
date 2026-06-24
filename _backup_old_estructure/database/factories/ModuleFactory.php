<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Module::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $name = ['edicion', 'renderizado', 'design', 'efectos'];
        $course_id = Course::inRandomOrder()->first()->id;
        $name = $this->faker->word();
        $created_at = $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
        $updated_at = $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
        $description = $this->faker->text($maxNbChars = 200);
        $status = 2;

        return [
            'id_courses' => $course_id,
            'name' => $name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'description' => $description,
            'status' => $status,
        ];
    }
}
