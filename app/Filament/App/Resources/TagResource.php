<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\TagResource\Pages;
use App\Models\News;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $modelLabel = 'здобич';

    protected static ?string $pluralModelLabel = 'здобич';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;


    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        // TODO: make compatible with any type of tagged records
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->width(160)
                        ->height(120)
                        ->extraImgAttributes(fn (News $record): array => ['alt' => $record->image_caption])
                        ->alignCenter()
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
                            ->formatStateUsing(fn($state) => html_entity_decode(Str::of($state)->stripTags()->words(80)))
                            ->copyable(),
                    ]),
                ])
                ->from('sm'),
            ])
            ->recordUrl(
                fn (News $record): string => NewsResource::getUrl('view', [
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
            // Important: persist this filter in the query string so pagination keeps it
            ->persistFiltersInSession()
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\Tags::route('/'),
            'view' => Pages\ListTags::route('/{slug}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_published', true);
    }
}
