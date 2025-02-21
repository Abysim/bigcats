<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class XArticleResource extends Resource
{
    protected static ?int $navigationSort = 0;

    protected static ?string $slug = '/';

    protected static ?string $model = Article::class;

    protected static ?string $modelLabel = 'головна';
    protected static ?string $pluralModelLabel = 'головна';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->extraImgAttributes(fn (Article $record): array => [
                                'alt' => $record->image_caption,
                            ])
                            ->width('100%')
                            ->height('auto')
                            ->hiddenLabel(),
                        TextEntry::make('content')
                            ->formatStateUsing(fn (string $state): HtmlString => new HtmlString($state))
                            ->size(TextEntrySize::Large)
                            ->hiddenLabel(),
                        RepeatableEntry::make('children')
                            ->schema([
                                TextEntry::make('title')
                                    ->url(fn (Article $record): string => self::getUrl('view', [
                                        'slug1' => $record->parent->slug,
                                        'slug2' => $record->slug,
                                    ]))
                                    ->size(TextEntrySize::Large)
                                    ->hiddenLabel(),
                                ImageEntry::make('image')
                                    ->extraImgAttributes(fn (Article $record): array => [
                                        'alt' => $record->image_caption,
                                    ])
                                    ->width('100%')
                                    ->height('auto')
                                    ->hiddenLabel(),
                                TextEntry::make('resume')
                                    ->hiddenLabel(),
                            ])
                            ->hiddenLabel()
                            ->grid(),
                    ])
                    ->columns(1)
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
            'index' => Pages\Frontpage::route('/'),
            'view' => Pages\ViewArticle::route('/{slug1?}/{slug2?}/{slug3?}/{slug4?}'),
        ];
    }
}
