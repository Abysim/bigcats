<?php

namespace App\Traits;

use Filament\Pages\BasePage;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * @method getHeading()
 */
trait HasCustomSEO
{
    protected function generateCanonicalUrl(): string
    {
        $canonicalUrl = url()->current();
        $pageParam = request()->query('page', 0);
        if ($pageParam > 1) {
            $canonicalUrl .= '?page=' . $pageParam;
        }
        return $canonicalUrl;
    }

    protected function registerSeoRenderHook(string $header, string $canonicalUrl): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => view('filament.seo-header', ['record' => new SEOData(
                title: $header . ' | ' . config('app.name'),
                url: $canonicalUrl,
                canonical_url: $canonicalUrl,
                openGraphTitle: $header,
            )])->render()
        );
    }

    public function registerSEO(): void
    {
        $canonicalUrl = $this->generateCanonicalUrl();
        $header = BasePage::getHeading() == $this->getHeading()
            ? BasePage::getHeading()
            : BasePage::getHeading() . ': ' . $this->getHeading();
        if ($canonicalUrl === url('/') && ($homepageTitle = config('seo.title.homepage_title'))) {
            $header = $homepageTitle;
        }

        $this->registerSeoRenderHook($header, $canonicalUrl);
    }
}
