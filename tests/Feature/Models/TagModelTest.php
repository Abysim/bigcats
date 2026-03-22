<?php

namespace Tests\Feature\Models;

use App\Models\News;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagModelTest extends TestCase
{
    use RefreshDatabase;

    private TagType $tagType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagType = TagType::factory()->create();
    }

    private function createTag(string $name, ?int $parentId = null): Tag
    {
        return Tag::factory()->create([
            'name' => $name,
            'parent_id' => $parentId,
            'type_id' => $this->tagType->id,
        ]);
    }

    public function test_tag_belongs_to_parent(): void
    {
        $parent = $this->createTag('Felidae');
        $child = $this->createTag('Panthera', $parent->id);

        $this->assertTrue($child->parent->is($parent));
    }

    public function test_tag_has_many_children(): void
    {
        $parent = $this->createTag('Big Cats');
        $this->createTag('Lions', $parent->id);
        $this->createTag('Tigers', $parent->id);

        $parent->load('children');

        $this->assertCount(2, $parent->children);
    }

    public function test_tag_belongs_to_type(): void
    {
        $tag = $this->createTag('Lion');

        $this->assertTrue($tag->type->is($this->tagType));
    }

    public function test_tag_belongs_to_many_news(): void
    {
        $tag = $this->createTag('Wildlife');
        $news = News::withoutEvents(fn () => News::factory()->create());

        $tag->news()->attach($news);
        $tag->load('news');

        $this->assertCount(1, $tag->news);
        $this->assertTrue($tag->news->contains($news));
    }

    public function test_tag_belongs_to_many_photos(): void
    {
        $tag = $this->createTag('Photography');
        $photo = Photo::factory()->create();

        $tag->photos()->attach($photo);
        $tag->load('photos');

        $this->assertCount(1, $tag->photos);
        $this->assertTrue($tag->photos->contains($photo));
    }

    public function test_get_all_children_ids_returns_self_for_leaf(): void
    {
        $tag = $this->createTag('Leaf Tag');

        $ids = $tag->getAllChildrenIds();

        $this->assertCount(1, $ids);
        $this->assertTrue($ids->contains($tag->id));
    }

    public function test_get_all_children_ids_returns_nested_hierarchy(): void
    {
        $root = $this->createTag('Root');
        $child1 = $this->createTag('Child One', $root->id);
        $child2 = $this->createTag('Child Two', $root->id);
        $grandchild = $this->createTag('Grandchild', $child1->id);

        $ids = $root->getAllChildrenIds();

        $this->assertCount(4, $ids);
        $this->assertTrue($ids->contains($root->id));
        $this->assertTrue($ids->contains($child1->id));
        $this->assertTrue($ids->contains($child2->id));
        $this->assertTrue($ids->contains($grandchild->id));
    }

    public function test_tag_generated_short_name(): void
    {
        $tag = $this->createTag('Amur Tiger');
        $tag->refresh();

        $this->assertEquals('AmurTiger', $tag->short_name);
    }

    public function test_tag_short_name_strips_special_chars(): void
    {
        $tag = $this->createTag("St. Peter's (Zoo)");
        $tag->refresh();

        $this->assertEquals('StPetersZoo', $tag->short_name);
    }

    public function test_tag_type_has_many_tags(): void
    {
        $this->createTag('Asia');
        $this->createTag('Africa');

        $this->tagType->load('tags');

        $this->assertCount(2, $this->tagType->tags);
    }
}
