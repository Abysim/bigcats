<?php

namespace App\Filament\App\Resources\NewsResource\Pages;

use App\Filament\App\Resources\NewsResource;
use App\Filament\App\Resources\TagResource\Widgets\TagCloud;
use App\Traits\HasCustomSEO;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListNews extends ListRecords
{
    use HasCustomSEO;

    protected static string $resource = NewsResource::class;

    public string $yearSlug;

    public string $monthSlug;

    public string $daySlug;

    public function mount(): void
    {
        parent::mount(); // Call parent mount method if needed
        $this->yearSlug = request('year', '');
        $this->monthSlug = request('month', '');
        $this->daySlug = request('day', '');

        $this->registerSEO();
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => '<link rel="alternate" type="application/rss+xml" title="Новини" href="/feed.xml">'
        );
    }

    protected function getTableQuery(): ?Builder
    {
        $year = $this->yearSlug;
        $month = $this->monthSlug;
        $day = $this->daySlug;

        $query = static::getResource()::getEloquentQuery();
        if ($year || $month || $day) {
            $query->whereHas('tags', function ($query) use ($year, $month, $day) {
                if ($year) {
                    $query->where('year', $year);
                }
                if ($month) {
                    $query->where('month', $month);
                }
                if ($day) {
                    $query->where('day', $day);
                }
            });
        }

        return $query;
    }

    public function getHeading(): string
    {
        $result = '';
        if ($this->yearSlug) {
            $result .= $this->yearSlug;
        }
        if ($this->monthSlug) {
            $result = Carbon::createFromFormat('m', $this->monthSlug)->translatedFormat('F') . ' ' . $result;
        }
        if ($this->daySlug) {
            $result = Carbon::createFromFormat(
                'd-m-Y', $this->daySlug . '-' . $this->monthSlug . '-' . $this->yearSlug
            )->translatedFormat('j F Y');
        }
        if (empty($result)) {
            return parent::getHeading();
        }

        return $result;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBreadcrumbs(): array
    {
        if ($this->yearSlug || $this->monthSlug || $this->daySlug) {
            $resource = static::getResource();
            $link = $resource::getUrl();
            $breadcrumbs = [
                $link => $resource::getBreadcrumb(),
            ];

            if ($this->monthSlug) {
                $breadcrumbs[$link . '/' . $this->yearSlug] = $this->yearSlug;
            }
            if ($this->daySlug) {
                $breadcrumbs[$link . '/' . $this->yearSlug . '/' . $this->monthSlug] =
                    Carbon::createFromFormat('m', $this->monthSlug)->translatedFormat('F');
            }
            $breadcrumbs[] = '';

            return $breadcrumbs;
        }

        return [];
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
