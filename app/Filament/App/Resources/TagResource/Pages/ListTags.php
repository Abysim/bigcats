<?php

namespace App\Filament\App\Resources\TagResource\Pages;

use App\Filament\App\Resources\TagResource;
use App\Models\Tag;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    public string $tagSlug;

    public function mount(): void
    {
        parent::mount(); // Call parent mount method if needed
        $this->tagSlug = request('slug', '');
    }

    protected function getTableQuery(): ?Builder
    {
        $slug = $this->tagSlug;
        $query = static::getResource()::getEloquentQuery();
        if ($slug) {
            $query->whereHas('tags', function ($query) use ($slug) {
                $query->where('slug', $slug);
            });
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        try {
            return Tag::where('slug', $this->tagSlug)->firstOrFail()->name;
        } catch (Throwable) {
            return parent::getHeading();
        }
    }

    public function getBreadcrumbs(): array
    {
        return ['Теґ', ''];
    }
}
