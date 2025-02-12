<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Article;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'parent_id' => Article::inRandomOrder()->first()->id ?? null,
            'slug' => fake()->slug(),
            'priority' => fake()->randomDigit(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'image' => fake()->word(),
            'image_caption' => fake()->word(),
            'is_published' => fake()->boolean(),
        ];
    }
}
