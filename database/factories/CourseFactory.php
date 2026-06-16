<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user_id = User::inRandomOrder()->first()->id;
        $id_categories = Category::inRandomOrder()->first()->id;
        $title = $this->faker->word();
        $area = 'vacio';
        $description = $this->faker->text($maxNbChars = 200);
        // random price 
        $price = $this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1000);
        $ranking_by_user = $this->faker->numberBetween(0, 5);
        $status = 2;
        $created_at = $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
        $updated_at = $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d H:i:s');
        $portada = $this->faker->imageUrl($width = 640, $height = 480);
        $url_portada = $this->faker->imageUrl($width = 640, $height = 480);
        $course_about = $this->faker->text($maxNbChars = 200);
        $will_learn = $this->faker->text($maxNbChars = 200);
        $prev_knowledge = $this->faker->text($maxNbChars = 200);
        $course_for = $this->faker->text($maxNbChars = 200);
        $course_time = 0;
        $course_level_id = $this->faker->numberBetween(1, 3);


        return [
            'user_id' => $user_id,
            'id_categories' => $id_categories,
            'title' => $title,
            'area' => $area,
            'description' => $description,
            'price' => $price,
            'ranking_by_user' => $ranking_by_user,
            'status' => $status,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'portada' => $portada,
            'url_portada' => $url_portada,
            'course_about' => $course_about,
            'will_learn' => $will_learn,
            'prev_knowledge' => $prev_knowledge,
            'course_for' => $course_for,
            'course_time' => $course_time,
            'course_level_id' => $course_level_id,
        ];
    }
}
