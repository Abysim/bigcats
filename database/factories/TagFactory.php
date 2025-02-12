<?php

namespace Database\Factories;

use App\Models\TagType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Tag;

class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->slug(3),
            'name' => fake()->sentence(3),
            'parent_id' => Tag::inRandomOrder()->first()->id ?? null,
            'type_id' => TagType::factory(),
        ];
    }
}
