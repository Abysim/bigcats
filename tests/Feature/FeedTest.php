<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_feed_returns_ok(): void
    {
        News::withoutEvents(fn () => News::factory()->create([
            'is_published' => true,
            'is_original' => true,
        ]));

        $response = $this->get('/news.xml');

        $response->assertStatus(200);
    }

    public function test_news_feed_contains_published_news_title(): void
    {
        News::withoutEvents(fn () => News::factory()->create([
            'title' => 'Unique Feed Test Title 12345',
            'is_published' => true,
            'is_original' => true,
        ]));

        $response = $this->get('/news.xml');

        $response->assertStatus(200);
        $response->assertSee('Unique Feed Test Title 12345');
    }

    public function test_news_feed_excludes_unpublished_news(): void
    {
        News::withoutEvents(fn () => News::factory()->create([
            'title' => 'Secret Unpublished Article XYZ',
            'is_published' => false,
        ]));

        $response = $this->get('/news.xml');

        $response->assertStatus(200);
        $response->assertDontSee('Secret Unpublished Article XYZ');
    }
}
