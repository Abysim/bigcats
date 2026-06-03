<?php

namespace Tests\Feature\Api;

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoApiTest extends TestCase
{
    use RefreshDatabase, ApiTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuth();
    }

    private function validPhotoPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Photo',
            'author_name' => 'Test Author',
            'flickr_link' => 'https://www.flickr.com/photos/12345678',
            'thumbnail_url' => 'https://live.staticflickr.com/1234/photo.jpg',
            'thumbnail_width' => 640,
            'thumbnail_height' => 480,
            'tags' => ['TestTag'],
        ], $overrides);
    }

    public function test_create_photo_requires_authentication(): void
    {
        $response = $this->postJson('/api/photos/create', []);

        $response->assertStatus(401);
    }

    public function test_create_photo_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/photos/create', []);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    public function test_create_photo_validates_field_types(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/photos/create', [
            'name' => str_repeat('x', 256),
            'author_name' => '',
            'flickr_link' => 'not-a-url',
            'thumbnail_url' => 'not-a-url',
            'thumbnail_width' => 0,
            'thumbnail_height' => -1,
            'tags' => [],
        ]);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    public function test_create_photo_successfully(): void
    {
        $this->createTag('Snow Leopard');

        $response = $this->withToken($this->token)->postJson('/api/photos/create',
            $this->validPhotoPayload(['tags' => ['SnowLeopard']]),
        );

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'flickr_link' => 'https://www.flickr.com/photos/12345678',
            ]);

        $this->assertDatabaseHas('photos', [
            'name' => 'Test Photo',
            'author_name' => 'Test Author',
            'flickr_link' => 'https://www.flickr.com/photos/12345678',
            'is_published' => true,
        ]);
    }

    public function test_create_photo_syncs_tags(): void
    {
        $tag1 = $this->createTag('Jaguar');
        $tag2 = $this->createTag('Brazil');

        $this->withToken($this->token)->postJson('/api/photos/create',
            $this->validPhotoPayload([
                'flickr_link' => 'https://www.flickr.com/photos/99999999',
                'tags' => ['Jaguar', 'Brazil'],
            ]),
        );

        $photo = Photo::where('flickr_link', 'https://www.flickr.com/photos/99999999')->first();
        $this->assertNotNull($photo);
        $this->assertCount(2, $photo->tags);
        $this->assertTrue($photo->tags->contains($tag1));
        $this->assertTrue($photo->tags->contains($tag2));
    }

    public function test_create_photo_rejects_duplicate_flickr_link(): void
    {
        Photo::factory()->create(['flickr_link' => 'https://www.flickr.com/photos/duplicate']);
        $this->createTag('Tiger');

        $response = $this->withToken($this->token)->postJson('/api/photos/create',
            $this->validPhotoPayload([
                'flickr_link' => 'https://www.flickr.com/photos/duplicate',
                'tags' => ['Tiger'],
            ]),
        );

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'errors' => ['Photo with this Flickr link already exists'],
            ]);
    }

    public function test_create_photo_validates_tags_not_empty(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/photos/create',
            $this->validPhotoPayload(['tags' => []]),
        );

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }
}
