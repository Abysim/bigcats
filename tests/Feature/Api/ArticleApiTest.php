<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    // Inline to avoid a fixture file dependency; must be a valid JFIF header for finfo() detection
    private const FAKE_JPEG = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//2wBDAP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//AP//wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAAB//EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AVQP/2Q==';

    private User $user;
    private string $token;
    private Article $frontpage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;

        $this->frontpage = Article::factory()->create([
            'parent_id' => null,
            'slug' => 'frontpage',
            'title' => 'Frontpage',
            'is_published' => true,
        ]);

        Http::fake(['*' => Http::response(
            base64_decode(self::FAKE_JPEG),
            200,
            ['Content-Type' => 'image/jpeg'],
        )]);
        Storage::fake('public');
    }

    private function validArticlePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Test Article Title',
            'content' => 'Test article content',
            'image' => 'https://example.com/image.jpg',
            'image_caption' => 'A caption',
            'tags' => ['TestTag'],
        ], $overrides);
    }

    private function createTag(string $name): Tag
    {
        $tagType = TagType::factory()->create();

        return Tag::factory()->create(['name' => $name, 'parent_id' => null, 'type_id' => $tagType->id]);
    }

    public function test_create_article_requires_authentication(): void
    {
        $response = $this->postJson('/api/articles/create', []);

        $response->assertStatus(401);
    }

    public function test_create_article_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/articles/create', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['status', 'errors'])
            ->assertJson(['status' => 'error']);
    }

    public function test_create_article_validates_field_types(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/articles/create', [
            'title' => '',
            'content' => '',
            'image' => 'http://not-https.com/img.jpg',
            'image_caption' => '',
            'tags' => 'not-array',
        ]);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    public function test_create_article_validates_tag_items_are_strings(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload(['tags' => [123, null]]),
        );

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);
    }

    public function test_create_article_successfully(): void
    {
        $this->createTag('Amur Tiger');

        $response = $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload(['tags' => ['AmurTiger']]),
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['status', 'image', 'url']);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article Title',
            'slug' => 'test-article-title',
            'parent_id' => $this->frontpage->id,
            'is_published' => false,
        ]);
    }

    public function test_create_article_converts_markdown_to_html(): void
    {
        $this->createTag('Lions');

        $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload([
                'title' => 'Markdown Test',
                'content' => '**Bold** text and _italic_ text',
                'tags' => ['Lions'],
            ]),
        );

        $article = Article::where('title', 'Markdown Test')->first();
        $this->assertNotNull($article);
        $this->assertStringContainsString('<strong>Bold</strong>', $article->content);
        $this->assertStringContainsString('<em>italic</em>', $article->content);
    }

    public function test_create_article_generates_unique_slug_for_duplicates(): void
    {
        Article::factory()->create([
            'parent_id' => $this->frontpage->id,
            'slug' => 'duplicate-title',
            'title' => 'Duplicate Title',
        ]);

        $this->createTag('Leopard');

        $response = $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload([
                'title' => 'Duplicate Title',
                'tags' => ['Leopard'],
            ]),
        );

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('articles', [
            'title' => 'Duplicate Title',
            'slug' => 'duplicate-title-2',
            'parent_id' => $this->frontpage->id,
        ]);
    }

    public function test_create_article_syncs_tags(): void
    {
        $tag1 = $this->createTag('Bengal Tiger');
        $tag2 = $this->createTag('India');

        $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload([
                'title' => 'Tag Sync Test',
                'tags' => ['BengalTiger', 'India'],
            ]),
        );

        $article = Article::where('title', 'Tag Sync Test')->first();
        $this->assertNotNull($article);
        $this->assertCount(2, $article->tags);
        $this->assertTrue($article->tags->contains($tag1));
        $this->assertTrue($article->tags->contains($tag2));
    }

    public function test_create_article_saves_image(): void
    {
        $this->createTag('Leopard');

        $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload([
                'title' => 'Image Test',
                'image' => 'https://example.com/photo.jpg',
                'tags' => ['Leopard'],
            ]),
        );

        $article = Article::where('title', 'Image Test')->first();
        $this->assertNotNull($article);
        $this->assertNotEmpty($article->image);
        $this->assertStringStartsWith('articles/', $article->image);
        Storage::disk('public')->assertExists($article->image);
    }

    public function test_create_article_fails_when_frontpage_missing(): void
    {
        $this->frontpage->delete();

        $response = $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload(),
        );

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'errors' => ['Frontpage article not found'],
            ]);
    }

    public function test_create_article_fails_when_image_download_fails(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*' => Http::response('Not Found', 404)]);

        $response = $this->withToken($this->token)->postJson('/api/articles/create',
            $this->validArticlePayload(),
        );

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'errors' => ['Failed to save image'],
            ]);

        $this->assertDatabaseMissing('articles', [
            'title' => 'Test Article Title',
        ]);
    }
}
