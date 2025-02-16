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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $recordTitleAttribute = 'title';

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
                Infolists\Components\Split::make([
                    Infolists\Components\TextEntry::make('date')
                        ->dateTime('j F Y')
                        ->label('Дата:')
                        ->grow(false)
                        ->inlineLabel(),
                    Infolists\Components\Split::make([]),
                    Infolists\Components\TextEntry::make('source_name')
                        ->formatStateUsing(fn (string $state, News $record): HtmlString => new HtmlString(
                            $record->source_url
                                ? "<a rel=\"nofollow\" title=\"$record->source_url\" href=\"$record->source_url\" target=\"_blank\">$state</a>"
                                : $state
                        ))
                        ->grow(false)
                        ->label('Джерело:')
                        ->inlineLabel(),
                ]),
                Infolists\Components\ImageEntry::make('image')
                    ->extraImgAttributes(fn (News $record): array => [
                        'alt' => $record->image_caption,
                    ])
                    ->width('100%')
                    ->height('auto')
                    ->hiddenLabel(),
                Infolists\Components\TextEntry::make('content')
                    ->formatStateUsing(fn (string $state): HtmlString => new HtmlString($state))
                    ->hiddenLabel(),
                Infolists\Components\Split::make([
                    Infolists\Components\TextEntry::make('tags')
                        ->label('Теґи:')
                        ->grow(false)
                        ->inlineLabel()
                        ->formatStateUsing(function (News $record): HtmlString {
                            $tagLinks = [];
                            foreach ($record->tags as $tag) {
                                $tagLinks[] = "<a rel=\"tag\" href=\"/tags/$tag->slug\">$tag->name</a>";
                            }
                            return new HtmlString(implode(', ', $tagLinks));
                        }),
                    Infolists\Components\Split::make([]),
                ]),
            ])
            ->columns(1);
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
