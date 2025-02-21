<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\XArticleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewArticle extends ViewRecord
{
    protected static string $resource = XArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(int|string|null $record = null): void
    {
        $this->record = $this->resolveRecord($record);

        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn(): string => seo($this->record));
    }

    protected function resolveRecord(int|string|null $key = null): Model
    {
        $record = null;
        for ($i = 1; $i <= 4; $i++) {
            $slug = request('slug' . $i, '');
            if (empty($slug)) {
                break;
            }

            $model = static::getModel()::query()
                ->where('parent_id', $record->id ?? null)
                ->where('is_published', true)
                ->where('slug', $slug)
                ->first();

            if (empty($model)) {
                break;
            }

            $record = $model;
        }

        if ($record === null) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        return $record;
    }

    public function getHeading(): string
    {
        return $this->record->title ?? parent::getHeading();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
