<?php

namespace App\Providers\Filament;

use App\Filament\App\Resources\NewsResource;
use App\Filament\App\Resources\PhotoResource;
use App\Models\Article;
use Cmsmaxinc\FilamentErrorPages\FilamentErrorPagesPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MarcoGermani87\FilamentCookieConsent\FilamentCookieConsent;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $articleItems = $this->buildArticleNavItems();

                return $builder
                    ->group(
                        NavigationGroup::make('Головна')
                            ->items($articleItems)
                    )
                    ->group(
                        NavigationGroup::make()
                            ->items([
                                ...NewsResource::getNavigationItems(),
                                ...PhotoResource::getNavigationItems(),
                            ])
                    );
            })
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->favicon(asset('images/icon.png'))
            ->viteTheme('resources/css/filament/app/theme.css')
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->renderHook(PanelsRenderHook::BODY_END, fn() => view('filament.app.custom-footer'))
            ->brandLogo(asset('images/full-logo.png'))
            ->darkModeBrandLogo(asset('images/full-logo-dark.png'))
            ->brandLogoHeight('3rem')
            ->plugins([
                FilamentErrorPagesPlugin::make(),
                FilamentCookieConsent::make(),
            ])
            ->topNavigation();
    }

    private function buildArticleNavItems(): array
    {
        try {
            $frontpage = Cache::remember(Article::NAV_CACHE_KEY, 300, fn() =>
                Article::whereNull('parent_id')
                    ->with('featuredChildren.featuredChildren')
                    ->first()
            );
        } catch (\Exception) {
            return [];
        }

        $navItems = [];
        if ($frontpage) {
            foreach ($frontpage->featuredChildren as $child) {
                $item = NavigationItem::make($child->title)
                    ->url($child->getUrl())
                    ->sort($child->priority);

                if ($child->featuredChildren->isNotEmpty()) {
                    $item->childItems(
                        $child->featuredChildren->map(fn($gc) =>
                            NavigationItem::make($gc->title)
                                ->url($gc->getUrl())
                        )->all()
                    );
                }
                $navItems[] = $item;
            }
        }

        return $navItems;
    }
}
