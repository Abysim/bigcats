<?php

namespace App\Filament\App\Resources\ArticleResource\Pages;

use App\Filament\App\Resources\XArticleResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ViewArticle extends ViewRecord
{
    protected static string $resource = XArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function resolveRecord(int | string $key): Model
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
}
