<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\XArticleResource;
use App\Models\Article;
use App\Traits\HasLatestNewsFooter;
use Filament\Resources\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class Frontpage extends Page
{
    use HasLatestNewsFooter;

    protected static string $resource = XArticleResource::class;

    protected static string $view = 'filament.app.resources.article-resource.pages.frontpage';

    public ?Article $article = null;

    public function mount(): void
    {
        $this->article = Article::frontpage()
            ->with('featuredChildren')
            ->first();

        $this->article?->wireChildrenParent('featuredChildren');

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

}
