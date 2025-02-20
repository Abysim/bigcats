<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\NewsResource\Widgets\LatestNews;
use App\Filament\App\Resources\TagResource\Widgets\TagCloud;
use App\Filament\App\Resources\XArticleResource;
use App\Traits\HasCustomSEO;
use Filament\Resources\Pages\Page;

class Frontpage extends Page
{
    use HasCustomSEO;

    protected static ?string $title = 'Останні новини';

    protected static string $resource = XArticleResource::class;

    protected static string $view = 'filament.app.resources.article-resource.pages.frontpage';

    public function mount(): void
    {
        $this->registerSEO();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LatestNews::make([
                'header' => '',
                'count' => 12,
                'grid' => [
                    'default' => 2,
                    'md' => 3,
                    'xl' => 4,
                ]
            ]),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getFooterWidgets(): array
    {
        return [
            TagCloud::make([
                'relation' => 'news',
            ]),
        ];

    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}
