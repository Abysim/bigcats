<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\XArticleResource;
use App\Models\Article;
use App\Traits\HasLatestNewsFooter;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewArticle extends ViewRecord
{
    use HasLatestNewsFooter;

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
        $frontpage = static::getModel()::query()->frontpage()->first();

        if (!$frontpage) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        $chain = [];
        $parentId = $frontpage->id;
        for ($i = 1; $i <= Article::MAX_DEPTH; $i++) {
            $slug = request('slug' . $i, '');
            if (empty($slug)) {
                break;
            }

            $model = static::getModel()::query()
                ->where('parent_id', $parentId)
                ->published()
                ->where('slug', $slug)
                ->first();

            if (!$model) {
                throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
            }

            $chain[] = $model;
            $parentId = $model->id;
        }

        if (empty($chain)) {
            throw (new ModelNotFoundException)->setModel($this->getModel(), [$key]);
        }

        $chain[0]->setRelation('parent', $frontpage);
        for ($i = 1; $i < count($chain); $i++) {
            $chain[$i]->setRelation('parent', $chain[$i - 1]);
        }

        return end($chain);
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

}
