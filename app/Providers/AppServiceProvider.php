<?php

namespace App\Providers;

use App\Models\Article;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Route::feeds();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceHttps($this->app->isProduction());

        // Clear navigation cache when articles change
        Article::saved(fn() => Cache::forget(Article::NAV_CACHE_KEY));
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
