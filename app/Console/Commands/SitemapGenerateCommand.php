<?php
/**
 * @author Andrii Kalmus <andrii.kalmus@abysim.com>
 */

namespace App\Console\Commands;

use App\Models\News;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapGenerateCommand extends Command
{
    protected $signature = 'sitemap:generate {timestamp?}';

    protected $description = 'Generate the sitemap.';

    public function handle(): void
    {
        $lastUpdateDate = $this->argument('timestamp')
            ? Carbon::createFromTimestamp($this->argument('timestamp'))
            : (News::latest('created_at')->first()?->created_at ?? now());

        Sitemap::create()
            ->add(Url::create('/')->setLastModificationDate($lastUpdateDate))
            ->add(Url::create('/news')->setLastModificationDate($lastUpdateDate))
            ->add(News::where(['is_published' => true, 'is_original' => true])->orderBy('date', 'desc')->get())
            ->add(Tag::whereHas('news', function ($query) {
                $query->where(['is_published' => true]);
            })->withCount('news')->having('news_count', '>', 4)->orderByDesc('news_count')->get())
            ->writeToFile(public_path('sitemap.xml'));

        Log::info('Sitemap generated: ' . $lastUpdateDate);
    }
}
