<?php

namespace Tests\Feature\Models;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_belongs_to_parent(): void
    {
        $parent = Article::factory()->create(['parent_id' => null]);
        $child = Article::factory()->create(['parent_id' => $parent->id]);

        $this->assertTrue($child->parent->is($parent));
    }

    public function test_article_has_many_children(): void
    {
        $parent = Article::factory()->create(['parent_id' => null]);
        Article::factory()->create(['parent_id' => $parent->id, 'slug' => 'child-1']);
        Article::factory()->create(['parent_id' => $parent->id, 'slug' => 'child-2']);

        $parent->load('children');

        $this->assertCount(2, $parent->children);
    }

    public function test_article_get_all_children_ids(): void
    {
        $root = Article::factory()->create(['parent_id' => null]);
        $child = Article::factory()->create(['parent_id' => $root->id]);
        $grandchild = Article::factory()->create(['parent_id' => $child->id]);

        $ids = $root->getAllChildrenIds();

        $this->assertCount(3, $ids);
        $this->assertTrue($ids->contains($root->id));
        $this->assertTrue($ids->contains($child->id));
        $this->assertTrue($ids->contains($grandchild->id));
    }

    public function test_article_casts_boolean_fields(): void
    {
        $article = Article::factory()->make(['is_published' => 1]);

        $this->assertTrue($article->is_published);
    }

    public function test_article_casts_integer_fields(): void
    {
        $article = Article::factory()->make(['priority' => '5']);

        $this->assertIsInt($article->priority);
        $this->assertEquals(5, $article->priority);
    }

    public function test_article_without_parent(): void
    {
        $article = Article::factory()->create(['parent_id' => null]);

        $this->assertNull($article->parent);
    }
}
