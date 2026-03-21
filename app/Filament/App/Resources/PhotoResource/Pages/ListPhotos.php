<?php

namespace App\Filament\App\Resources\PhotoResource\Pages;

use App\Traits\HasCustomSEO;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Computed;

class ListPhotos extends Page
{
    use HasCustomSEO;

    protected const PAGE_SIZE = 48;

    private const GALLERY_FIELDS = ['id', 'name', 'author_name', 'flickr_link', 'thumbnail_url', 'thumbnail_width', 'thumbnail_height', 'created_at'];

    protected static ?string $title = 'Фотогалерея';

    protected static string $resource = \App\Filament\App\Resources\PhotoResource::class;

    protected static string $view = 'filament.app.resources.photo-resource.pages.list-photos';

    public ?string $cursorCreatedAt = null;

    public ?int $cursorId = null;

    public bool $hasMore = false;

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
        $this->dispatch('photos-loaded', photos: $this->fetchBatch());
    }

    #[Computed]
    public function initialPhotos(): array
    {
        if ($this->cursorCreatedAt !== null) {
            return [];
        }

        return $this->fetchBatch();
    }

    protected function fetchBatch(): array
    {
        $query = static::getResource()::getEloquentQuery()
            ->select(self::GALLERY_FIELDS)
            ->reorder()->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        if ($this->cursorCreatedAt !== null) {
            $query->whereRaw('(created_at, id) < (?, ?)', [$this->cursorCreatedAt, $this->cursorId]);
        }

        $batch = $query->take(self::PAGE_SIZE + 1)->toBase()->get();

        $this->hasMore = $batch->count() > self::PAGE_SIZE;
        if ($this->hasMore) {
            $batch->pop();
        }

        if ($batch->isNotEmpty()) {
            $last = $batch->last();
            $this->cursorCreatedAt = $last->created_at;
            $this->cursorId = $last->id;
        }

        return $batch->map(fn ($photo) => [
            'id' => $photo->id,
            'name' => $photo->name,
            'author_name' => $photo->author_name,
            'flickr_link' => $photo->flickr_link,
            'thumbnail_url' => $photo->thumbnail_url,
            'thumbnail_width' => (int) $photo->thumbnail_width,
            'thumbnail_height' => (int) $photo->thumbnail_height,
        ])->all();
    }
}
