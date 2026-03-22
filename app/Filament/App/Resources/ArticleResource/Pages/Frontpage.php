<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\NewsResource\Widgets\LatestNews;
use App\Filament\App\Resources\XArticleResource;
use App\Models\Article;
use Filament\Resources\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class Frontpage extends Page
{
    protected static string $resource = XArticleResource::class;

    protected static string $view = 'filament.app.resources.article-resource.pages.frontpage';

    public ?Article $article = null;

    public function mount(): void
    {
        $this->article = Article::whereNull('parent_id')
            ->with('featuredChildren')
            ->first();

        if ($this->article) {
            FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn(): string => seo($this->article));
        }
    }

    public function getTitle(): string
    {
        return $this->article?->title ?? '';
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestNews::make([
                'count' => 6,
            ]),
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 1,
        ];
    }
}
