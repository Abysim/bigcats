<?php

namespace App\Filament\App\Resources\TagResource\Pages;

use App\Filament\App\Resources\TagResource;
use App\Filament\App\Resources\TagResource\Widgets\TagCloud;
use App\Models\Tag;
use App\Traits\HasCustomSEO;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class ListTags extends ListRecords
{
    use HasCustomSEO;

    protected static string $resource = TagResource::class;

    protected ?string $heading = 'Теґ';

    public string $tagSlug;

    public function mount(): void
    {
        parent::mount(); // Call parent mount method if needed
        $this->tagSlug = request('slug', '');

        $this->registerSEO();
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
        return [static::getResource()::getUrl() => 'Теґи', ''];
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
