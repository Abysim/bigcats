<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\NewsResource\Widgets\LatestNews;
use App\Filament\App\Resources\XArticleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewArticle extends ViewRecord
{
    protected static string $resource = XArticleResource::class;

    protected static string $view = 'filament.app.resources.article-resource.pages.view-article';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(int|string|null $record = null): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->load('publishedChildren');

        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn(): string => seo($this->record));
    }

    protected function resolveRecord(int|string|null $key = null): Model
    {
        $parentId = static::getModel()::query()->whereNull('parent_id')->value('id');

        if (!$parentId) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        $record = null;
        for ($i = 1; $i <= 6; $i++) {
            $slug = request('slug' . $i, '');
            if (empty($slug)) {
                break;
            }

            $model = static::getModel()::query()
                ->where('parent_id', $parentId)
                ->where('is_published', true)
                ->where('slug', $slug)
                ->first();

            if (!$model) {
                break;
            }

            $record = $model;
            $parentId = $model->id;
        }

        if (!$record) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        // Verify all slug segments were consumed — /lion/nonexistent must 404
        for ($j = $i; $j <= 6; $j++) {
            if (!empty(request('slug' . $j))) {
                throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
            }
        }

        return $record;
    }

    public function getHeading(): string
    {
        return $this->record->title ?? parent::getHeading();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [url('/') => 'Головна'];

        $ancestors = $this->record->getAncestors();
        foreach ($ancestors as $ancestor) {
            if (!$ancestor->isFrontpage()) {
                $breadcrumbs[url($ancestor->getUrl())] = $ancestor->title;
            }
        }

        return $breadcrumbs;
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
