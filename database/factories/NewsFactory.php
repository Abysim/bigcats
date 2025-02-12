<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\News;

class NewsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = News::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'date' => fake()->date(),
            'slug' => fake()->slug(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'source_name' => fake()->word(),
            'source_url' => fake()->regexify('[A-Za-z0-9]{1024}'),
            'image' => fake()->word(),
            'image_caption' => fake()->word(),
            'author' => fake()->word(),
            'is_original' => fake()->boolean(),
            'is_published' => fake()->boolean(),
        ];
    }
}
