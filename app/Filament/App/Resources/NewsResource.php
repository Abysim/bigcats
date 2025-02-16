<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $modelLabel = 'новина';

    protected static ?string $pluralModelLabel = 'новини';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->width(160)
                        ->height(120)
                        ->extraImgAttributes(fn (News $record): array => [
                            'alt' => $record->image_caption,
                            'title' => $record->image_caption
                        ])
                        ->grow(false),
                    Stack::make([
                        Split::make([
                            TextColumn::make('date')
                                ->size(TextColumnSize::Medium)
                                ->date('d.m.Y')
                                ->grow(false),
                            TextColumn::make('title')
                                ->size(TextColumnSize::Medium)
                                ->weight(FontWeight::Bold)
                        ]),
                        TextColumn::make('content')
                            ->formatStateUsing(fn($state) => Str::of($state)->stripTags()->words(80))
                            ->copyable(),
                    ]),
                ]),
            ])
            ->recordUrl(
                fn (News $record): string => self::getUrl('view', [
                    'year' => $record->year,
                    'month' => $record->month,
                    'day' => $record->day,
                    'record' => $record->slug,
                ]),
            )
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('title'),
                Infolists\Components\TextEntry::make('slug'),
                Infolists\Components\TextEntry::make('content')
                    ->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'view' => Pages\ViewNews::route('/{year}/{month}/{day}/{record}'),
        ];
    }
}
