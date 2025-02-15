<?php
/**
 * @author Andrii Kalmus <andrii.kalmus@abysim.com>
 */

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PopulateNewsCommand extends Command
{
    protected $signature = 'populate:news';

    protected $description = 'Populate initial news data';

    public function handle(): void
    {
        $news = json_decode(File::get(storage_path('app/news-export.json')), true);

        foreach (array_reverse($news) as $item) {
            $model = News::updateOrCreate([
                'date' => $item['field_date'],
                'slug' => Str::afterLast($item['view_node'], '/'),
            ], [
                'is_original' => $item['type'] == 'Оригінальна новина',
                'is_published' => $item['status'],
                'slug' => Str::afterLast($item['view_node'], '/'),
                'image' => 'news/' . urldecode(Str::afterLast($item['field_image_1'], '/')),
                'image_caption' => $item['field_image'],
                'date' => $item['field_date'],
                'title' => html_entity_decode($item['title']),
                'source_url' => $item['field_source'],
                'source_name' => $item['field_source_1'],
                'content' => urldecode(Str::replace(
                    ['src="/sites/default/files/inline-images/', 'class="youtube-embed-wrapper"'],
                    ['src="/storage/news/', 'data-youtube-video="true" class="responsive"'],
                    $item['body']
                )),
            ]);

            $tagNames = explode(', ', $item['field_tags']);
            $model->tags()->sync(Tag::whereIn('name', $tagNames)->pluck('id'));
        }
    }
}
