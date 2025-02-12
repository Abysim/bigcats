<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\News;
use App\Models\Tag;
use App\Models\TagType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $types = TagType::factory(3)->create();
        $tags = Tag::factory(100)->recycle($types)->create();
        News::factory(50)->recycle($tags)->create();
        Article::factory(100)->recycle($tags)->create();
    }
}
