<?php

namespace App\Filament\App\Resources\NewsResource\Pages;

use App\Filament\App\Resources\NewsResource;
use App\Filament\App\Resources\NewsResource\Widgets\LatestNews;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $os = '';
        if (!empty($this->record->source_url)) {
            $os = '<meta name="original-source" content="' . $this->record->source_url . '" />';
        }
        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn(): string => seo($this->record) . $os);
    }

    protected function resolveRecord(int | string $key): Model
    {
        return static::getModel()::query()
            ->where('year', request('year'))
            ->where('month', request('month'))
            ->where('day', request('day'))
            ->where('slug', $key)
            ->where('is_published', true)
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

    protected function getFooterWidgets(): array
    {
        return [
            LatestNews::make([
                'count' => 6,
            ]),
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 1,
        ];
    }
}
