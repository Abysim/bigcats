<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Infolists;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;

class XArticleResource extends Resource
{
    protected static ?string $slug = '/';

    protected static ?string $model = Article::class;

    protected static ?string $modelLabel = 'головна';
    protected static ?string $pluralModelLabel = 'головна';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Navigation handled by AppPanelProvider
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->extraImgAttributes(fn (Article $record): array => [
                                'alt' => addslashes($record->image_caption),
                            ])
                            ->width('100%')
                            ->height('auto')
                            ->hiddenLabel(),
                        Infolists\Components\TextEntry::make('image_caption')
                            ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                                '<span class="text-gray-500 dark:text-gray-400 italic">' . e($state) . '</span>'
                            ))
                            ->size(TextEntrySize::Small)
                            ->visible(fn (Article $record): bool => filled($record->image_caption))
                            ->hiddenLabel(),
                        Infolists\Components\TextEntry::make('content')
                            ->formatStateUsing(fn (string $state): HtmlString => new HtmlString($state))
                            ->size(TextEntrySize::Medium)
                            ->prose()
                            ->hiddenLabel(),
                        Infolists\Components\Split::make([
                            Infolists\Components\Split::make([]),
                            Infolists\Components\TextEntry::make('source_name')
                                ->formatStateUsing(fn (string $state, Article $record): HtmlString => new HtmlString(
                                    $record->source_url
                                        ? '<a rel="nofollow" title="' . e($record->source_url) . '" href="' . e($record->source_url) . '" target="_blank">' . e($state) . '</a>'
                                        : e($state)
                                ))
                                ->grow(false)
                                ->label('Джерело:')
                                ->inlineLabel(),
                        ])
                            ->visible(fn (Article $record): bool => filled($record->source_name)),
                        ViewEntry::make('children')
                            ->view('filament.app.resources.article-resource.components.children-grid')
                            ->hiddenLabel(),
                    ])
                    ->columns(1)
            ])
            ->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\Frontpage::route('/'),
            'view' => Pages\ViewArticle::route('/{slug1}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}'),
        ];
    }
}
