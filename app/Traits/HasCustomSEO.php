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

    protected function registerSeoRenderHook(string $header, string $canonicalUrl, bool $noIndex = false): void
    {
        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn(): string => seo(new SEOData(
            title: $header . ' | ' . config('app.name'),
            url: $canonicalUrl,
            robots: $noIndex ? 'noindex' : null,
            canonical_url: $canonicalUrl,
            openGraphTitle: $header,
        )));
    }

    public function registerSEO($noIndex = false): void
    {
        $canonicalUrl = $this->generateCanonicalUrl();
        $header = BasePage::getHeading() == $this->getHeading()
            ? BasePage::getHeading()
            : BasePage::getHeading() . ': ' . $this->getHeading();
        if ($canonicalUrl === url('/') && ($homepageTitle = config('seo.title.homepage_title'))) {
            $header = $homepageTitle;
        }

        $this->registerSeoRenderHook($header, $canonicalUrl, $noIndex);
    }
}
