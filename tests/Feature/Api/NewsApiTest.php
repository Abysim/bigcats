<?php

namespace Tests\Feature\Api;

use App\Models\News;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;

        Http::fake(['*' => Http::response('fake-image-content')]);
        Storage::fake('public');
    }

    private function validNewsPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test News Title',
            'date' => '2025-07-01',
            'content' => 'Test content',
            'image' => 'https://example.com/image.jpg',
            'image_caption' => 'A caption',
            'is_original' => true,
            'tags' => ['TestTag'],
        ], $overrides);
    }

    private function createTag(string $name): Tag
    {
        $tagType = TagType::factory()->create();

        return Tag::factory()->create(['name' => $name, 'parent_id' => null, 'type_id' => $tagType->id]);
    }

    public function test_create_news_requires_authentication(): void
    {
        $response = $this->postJson('/api/news/create', []);

        $response->assertStatus(401);
    }

    public function test_create_news_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/news/create', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['status', 'errors'])
            ->assertJson(['status' => 'error']);
    }

    public function test_create_news_validates_field_types(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/news/create', [
            'title' => '',
            'content' => '',
            'date' => 'not-a-date',
            'image' => 'not-a-url',
            'image_caption' => '',
            'is_original' => 'not-bool',
            'tags' => 'not-array',
        ]);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    public function test_create_news_rejects_duplicate_title_and_date(): void
    {
        News::withoutEvents(fn () => News::factory()->create([
            'title' => 'Tiger Spotted',
            'date' => '2025-06-15',
        ]));

        $response = $this->withToken($this->token)->postJson('/api/news/create',
            $this->validNewsPayload(['title' => 'Tiger Spotted', 'date' => '2025-06-15']),
        );

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'errors' => ['News with this title and date already exists'],
            ]);
    }

    public function test_create_news_successfully(): void
    {
        $this->createTag('Amur Tiger');

        $response = $this->withToken($this->token)->postJson('/api/news/create',
            $this->validNewsPayload([
                'title' => 'New Tiger Found',
                'source_url' => 'https://example.com/source',
                'source_name' => 'Example News',
                'author' => 'John Doe',
                'tags' => ['AmurTiger'],
            ]),
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['status', 'image', 'url']);

        $this->assertDatabaseHas('news', [
            'title' => 'New Tiger Found',
            'slug' => 'new-tiger-found',
            'is_published' => true,
            'is_original' => true,
        ]);
    }

    public function test_create_news_converts_markdown_to_html(): void
    {
        $this->createTag('Lions');

        $this->withToken($this->token)->postJson('/api/news/create',
            $this->validNewsPayload([
                'title' => 'Markdown Test',
                'content' => '**Bold** text and _italic_ text',
                'tags' => ['Lions'],
            ]),
        );

        $news = News::where('title', 'Markdown Test')->first();
        $this->assertNotNull($news);
        $this->assertStringContainsString('<strong>Bold</strong>', $news->content);
        $this->assertStringContainsString('<em>italic</em>', $news->content);
    }

    public function test_create_news_syncs_tags(): void
    {
        $tag1 = $this->createTag('Bengal Tiger');
        $tag2 = $this->createTag('India');

        $this->withToken($this->token)->postJson('/api/news/create',
            $this->validNewsPayload([
                'title' => 'Tag Sync Test',
                'tags' => ['BengalTiger', 'India'],
            ]),
        );

        $news = News::where('title', 'Tag Sync Test')->first();
        $this->assertNotNull($news);
        $this->assertCount(2, $news->tags);
        $this->assertTrue($news->tags->contains($tag1));
        $this->assertTrue($news->tags->contains($tag2));
    }

    public function test_create_news_saves_image(): void
    {
        $this->createTag('Leopard');

        $this->withToken($this->token)->postJson('/api/news/create',
            $this->validNewsPayload([
                'title' => 'Image Test',
                'image' => 'https://example.com/photo.jpg',
                'tags' => ['Leopard'],
            ]),
        );

        $news = News::where('title', 'Image Test')->first();
        $this->assertNotNull($news);
        $this->assertNotEmpty($news->image);
        $this->assertStringStartsWith('news/', $news->image);
        Storage::disk('public')->assertExists($news->image);
    }
}
