<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class UserExamHeaderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $probability  = rand(1, 10);
        if ($probability > 7) {
            $rate = $this->faker->numberBetween(0, 79); // 30% of the time
        } else {
            $rate = $this->faker->numberBetween(80, 100); // 70% of the time
        }

        $user_id = DB::table('users')->inRandomOrder()->first()->id;
        $productor_id = DB::table('users')->inRandomOrder()->first()->id;
        $status = 1;
        $exam_id = DB::table('exam')->inRandomOrder()->first()->id;

        // pre test
        // $created_at = $this->faker->dateTimeBetween($startDate = '-40 days', $endDate = '-28 days', $timezone = null);
        // $updated_at = $this->faker->dateTimeBetween($startDate = '-40 days', $endDate = '-28 days', $timezone = null);


        // // post test
        $created_at = $this->faker->dateTimeBetween($startDate = '-1 day', $endDate = 'now', $timezone = null);
        $updated_at = $this->faker->dateTimeBetween($startDate = '-1 day', $endDate = 'now', $timezone = null);

        if ($rate < 80) {
            $condition = "Disaproved";
        } else {
            $condition = "Aproved";
        }
        return [
            'user_id' => $user_id,
            'productor_id' => $productor_id,
            'rate' => $rate,
            'status' => $status,
            'exam_id' => $exam_id,
            'condition' => $condition,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
    }
}
