<?php

namespace App\Filament\App\Resources\PhotoResource\Pages;

use App\Traits\HasCustomSEO;
use Filament\Resources\Pages\Page;

class ListPhotos extends Page
{
    use HasCustomSEO;

    protected const PAGE_SIZE = 48;

    protected static ?string $title = 'Фотогалерея';

    protected static string $resource = \App\Filament\App\Resources\PhotoResource::class;

    protected static string $view = 'filament.app.resources.photo-resource.pages.list-photos';

    public int $perPage = self::PAGE_SIZE;

    public function mount(): void
    {
        $this->registerSEO();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function loadMore(): void
    {
        $this->perPage += self::PAGE_SIZE;
    }

    protected function getViewData(): array
    {
        $photos = static::getResource()::getEloquentQuery()
            ->take($this->perPage + 1)
            ->get();

        $hasMore = $photos->count() > $this->perPage;

        return [
            'photos' => $hasMore ? $photos->slice(0, $this->perPage) : $photos,
            'hasMore' => $hasMore,
        ];
    }
}
