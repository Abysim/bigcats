<?php

namespace App\Providers;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
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
