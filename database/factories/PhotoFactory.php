<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'author_name' => fake()->name(),
            'flickr_link' => 'https://www.flickr.com/photos/' . fake()->unique()->numerify('########'),
            'thumbnail_url' => 'https://live.staticflickr.com/' . fake()->numerify('####/##########') . '.jpg',
            'thumbnail_width' => fake()->numberBetween(200, 800),
            'thumbnail_height' => fake()->numberBetween(200, 800),
            'is_published' => true,
        ];
    }
}
