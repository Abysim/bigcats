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

    public array $photos = [];

    public bool $hasMore = false;

    public function mount(): void
    {
        $this->registerSEO();
        $this->loadBatch();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function loadMore(): void
    {
        $this->loadBatch();
    }

    protected function loadBatch(): void
    {
        $query = static::getResource()::getEloquentQuery()
            ->select(['id', 'name', 'author_name', 'flickr_link', 'thumbnail_url', 'thumbnail_width', 'thumbnail_height', 'created_at'])
            ->orderBy('id', 'desc');

        if (!empty($this->photos)) {
            $last = end($this->photos);
            $query->where(function ($q) use ($last) {
                $q->where('created_at', '<', $last['created_at'])
                  ->orWhere(function ($q2) use ($last) {
                      $q2->where('created_at', '=', $last['created_at'])
                         ->where('id', '<', $last['id']);
                  });
            });
        }

        $batch = $query->take(self::PAGE_SIZE + 1)->get();

        $this->hasMore = $batch->count() > self::PAGE_SIZE;
        if ($this->hasMore) {
            $batch->pop();
        }

        foreach ($batch as $photo) {
            $this->photos[] = [
                'id' => $photo->id,
                'name' => $photo->name,
                'author_name' => $photo->author_name,
                'flickr_link' => $photo->flickr_link,
                'thumbnail_url' => $photo->thumbnail_url,
                'thumbnail_width' => $photo->thumbnail_width,
                'thumbnail_height' => $photo->thumbnail_height,
                'created_at' => $photo->created_at->toDateTimeString(),
            ];
        }
    }
}
