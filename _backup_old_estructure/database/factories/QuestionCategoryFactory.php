<?php

namespace Database\Factories;

use App\Models\QuestionCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QuestionCategory>
 */
class QuestionCategoryFactory extends Factory
{
    protected $model = QuestionCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(12),
            'is_active' => fake()->boolean(80),
            'questions_count' => fake()->numberBetween(0, 25),
            'created_by' => User::query()->inRandomOrder()->value('id') ?? null,
            'updated_by' => User::query()->inRandomOrder()->value('id') ?? null,
        ];
    }
}
