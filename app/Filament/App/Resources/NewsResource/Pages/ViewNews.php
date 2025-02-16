<?php

namespace App\Filament\App\Resources\NewsResource\Pages;

use App\Filament\App\Resources\NewsResource;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ViewNews extends ViewRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => Filament::auth()->check())
                ->url(fn ($record) => route('filament.admin.resources.news.edit', $record)),
        ];
    }

    public function getHeading(): string
    {
        return $this->getRecordTitle();
    }

    protected function resolveRecord(int | string $key): Model
    {
        return static::getModel()::query()
            ->where('year', request('year'))
            ->where('month', request('month'))
            ->where('day', request('day'))
            ->where('slug', $key)
            ->firstOrFail();
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $link = $resource::getUrl();
        $breadcrumbs = [
            $link => $resource::getBreadcrumb(),
        ];

        $breadcrumbs[$link . '/' . request('year')] = request('year');
        $breadcrumbs[$link . '/' . request('year') . '/' . request('month')] =
            Carbon::createFromFormat('m', request('month'))->translatedFormat('F');
        $breadcrumbs[$link . '/' . request('year') . '/' . request('month') . '/' . request('day')] = request('day');
        $breadcrumbs[] = '';

        return $breadcrumbs;
    }
}
