<?php

namespace App\Filament\App\Resources\NewsResource\Widgets;

use App\Filament\App\Resources\NewsResource;
use App\Models\News;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestNews extends BaseWidget
{
    public ?string $header = 'Останні новини';

    public int $count = 10;

    public array $grid = [
        'default' => 2,
        'md' => 3,
        'lg' => 1,
    ];

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                News::query()
                    ->where('is_published', true)
                    ->orderBy('date', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->limit($this->count)
            )
            ->heading($this->header)
            ->columns([
                Stack::make([
                    ImageColumn::make('image')
                        ->width('100%')
                        ->height('auto')
                        ->extraImgAttributes(fn (News $record): array => ['alt' => addslashes($record->image_caption)]),
                    TextColumn::make('title')
                        ->formatStateUsing(
                            fn (News $record, string $state): string => $record->date->format('d.m.Y') . ': ' . $state
                        ),
                ]),
            ])
            ->contentGrid($this->grid)
            ->paginated(false)
            ->recordUrl(
                fn (News $record): string => NewsResource::getUrl('view', [
                    'year' => $record->year,
                    'month' => $record->month,
                    'day' => $record->day,
                    'record' => $record->slug,
                ]),
            );
    }
}
