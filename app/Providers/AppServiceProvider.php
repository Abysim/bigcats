<?php

namespace App\Providers;

use App\Models\Article;
use App\Sanitizers\VideoEmbedSrcSanitizer;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Route::feeds();

        $this->app->scoped(
            HtmlSanitizerInterface::class,
            fn (): HtmlSanitizer => new HtmlSanitizer(
                (new HtmlSanitizerConfig)
                    ->allowSafeElements()
                    ->allowElement('iframe', [
                        'src',
                        'width',
                        'height',
                        'allow',
                        'allowfullscreen',
                        'title',
                        'class',
                        'style',
                    ])
                    ->allowRelativeLinks()
                    ->allowRelativeMedias()
                    ->allowAttribute('class', allowedElements: '*')
                    ->allowAttribute('style', allowedElements: '*')
                    ->allowAttribute('data-youtube-video', allowedElements: ['div'])
                    ->withAttributeSanitizer(new VideoEmbedSrcSanitizer)
                    ->withMaxInputLength(500000),
            ),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceHttps($this->app->isProduction());

        Article::saved(function (Article $article) {
            if ($article->wasChanged(['title', 'slug', 'is_published', 'is_featured', 'priority', 'parent_id'])) {
                Cache::forget(Article::NAV_CACHE_KEY);
            }
        });
        Article::deleted(fn() => Cache::forget(Article::NAV_CACHE_KEY));

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Blue,
            'info' => Color::Orange,
            'primary' => Color::Yellow,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);
    }
}
