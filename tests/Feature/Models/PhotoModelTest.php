<?php

namespace Tests\Feature\Models;

use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_belongs_to_many_tags(): void
    {
        $tagType = TagType::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Puma', 'parent_id' => null, 'type_id' => $tagType->id]);
        $photo = Photo::factory()->create();

        $photo->tags()->attach($tag);
        $photo->load('tags');

        $this->assertCount(1, $photo->tags);
        $this->assertTrue($photo->tags->contains($tag));
    }

    public function test_photo_casts_dimensions_as_integers(): void
    {
        $photo = Photo::factory()->make([
            'thumbnail_width' => '640',
            'thumbnail_height' => '480',
        ]);

        $this->assertIsInt($photo->thumbnail_width);
        $this->assertIsInt($photo->thumbnail_height);
    }

    public function test_photo_casts_is_published_as_boolean(): void
    {
        $photo = Photo::factory()->make(['is_published' => 1]);

        $this->assertTrue($photo->is_published);
    }

    public function test_photo_fillable_attributes(): void
    {
        $data = [
            'name' => 'Test Photo',
            'author_name' => 'Test Author',
            'flickr_link' => 'https://flickr.com/test',
            'thumbnail_url' => 'https://flickr.com/thumb.jpg',
            'thumbnail_width' => 800,
            'thumbnail_height' => 600,
            'is_published' => true,
        ];

        $photo = new Photo($data);

        $this->assertEquals('Test Photo', $photo->name);
        $this->assertEquals('Test Author', $photo->author_name);
        $this->assertEquals('https://flickr.com/test', $photo->flickr_link);
        $this->assertEquals(800, $photo->thumbnail_width);
        $this->assertEquals(600, $photo->thumbnail_height);
    }
}
