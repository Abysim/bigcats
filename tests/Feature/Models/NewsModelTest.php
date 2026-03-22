<?php

namespace Tests\Feature\Models;

use App\Models\News;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_belongs_to_many_tags(): void
    {
        $news = News::withoutEvents(fn () => News::factory()->create());
        $tagType = TagType::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Test Tag', 'parent_id' => null, 'type_id' => $tagType->id]);

        $news->tags()->attach($tag);
        $news->load('tags');

        $this->assertCount(1, $news->tags);
        $this->assertTrue($news->tags->contains($tag));
    }

    public function test_get_feed_items_returns_only_published(): void
    {
        News::withoutEvents(function () {
            News::factory()->create(['is_published' => true, 'title' => 'Published News']);
            News::factory()->create(['is_published' => false, 'title' => 'Unpublished News']);
        });

        $items = News::getFeedItems();

        $this->assertTrue($items->every(fn (News $n) => $n->is_published));
        $this->assertTrue($items->contains(fn (News $n) => $n->title === 'Published News'));
        $this->assertFalse($items->contains(fn (News $n) => $n->title === 'Unpublished News'));
    }

    public function test_get_feed_items_limits_to_20(): void
    {
        News::withoutEvents(fn () => News::factory()->count(25)->create(['is_published' => true]));

        $items = News::getFeedItems();

        $this->assertLessThanOrEqual(20, $items->count());
    }

    public function test_get_feed_items_orders_by_date_desc(): void
    {
        News::withoutEvents(function () {
            News::factory()->create(['is_published' => true, 'date' => '2025-01-01']);
            News::factory()->create(['is_published' => true, 'date' => '2025-06-01']);
            News::factory()->create(['is_published' => true, 'date' => '2025-03-01']);
        });

        $items = News::getFeedItems();
        $dates = $items->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values();

        // Verify dates are in descending order
        for ($i = 0; $i < $dates->count() - 1; $i++) {
            $this->assertGreaterThanOrEqual($dates[$i + 1], $dates[$i]);
        }
    }

    public function test_get_image_p_returns_html_when_image_exists(): void
    {
        $news = News::factory()->make([
            'image' => 'news/test.jpg',
            'image_caption' => 'A tiger in the wild',
        ]);

        $result = $news->getImageP();

        $this->assertStringContainsString('<img', $result);
        $this->assertStringContainsString('<p>', $result);
        $this->assertStringContainsString('A tiger in the wild', $result);
    }

    public function test_get_image_p_returns_empty_when_no_image(): void
    {
        $news = News::factory()->make(['image' => null]);

        $this->assertEmpty($news->getImageP());
    }

    public function test_get_image_p_escapes_html_in_caption(): void
    {
        $news = News::factory()->make([
            'image' => 'news/test.jpg',
            'image_caption' => 'Tiger <script>alert("xss")</script>',
        ]);

        $result = $news->getImageP();

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_news_generated_columns(): void
    {
        $news = News::withoutEvents(fn () => News::factory()->create([
            'date' => '2025-07-15',
        ]));
        $news->refresh();

        $this->assertEquals('2025', $news->year);
        $this->assertEquals('07', $news->month);
        $this->assertEquals('15', $news->day);
    }

    public function test_news_casts_boolean_fields(): void
    {
        $news = News::factory()->make([
            'is_original' => 1,
            'is_published' => 0,
        ]);

        $this->assertTrue($news->is_original);
        $this->assertFalse($news->is_published);
    }

    public function test_news_casts_date_field(): void
    {
        $news = News::factory()->make(['date' => '2025-03-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $news->date);
        $this->assertEquals('2025-03-15', $news->date->format('Y-m-d'));
    }
}
