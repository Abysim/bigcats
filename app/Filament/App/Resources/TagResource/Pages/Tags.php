<?php

namespace App\Filament\App\Resources\TagResource\Pages;

use App\Filament\App\Resources\TagResource;
use App\Filament\App\Resources\TagResource\Widgets\TagCloud;
use App\Traits\HasCustomSEO;
use App\Traits\HasLatestNewsFooter;
use Filament\Resources\Pages\Page;

class Tags extends Page
{
    use HasCustomSEO, HasLatestNewsFooter;

    protected static ?string $title = 'Теґи';

    protected static string $resource = TagResource::class;

    protected static string $view = 'filament.app.resources.tag-resource.pages.tags';

    public function mount(): void
    {
        $this->registerSEO();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TagCloud::make([
                'relation' => 'news',
                'heading' => '',
                'maxSize' => 128,
                'minSize' => 16,
            ]),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

}
