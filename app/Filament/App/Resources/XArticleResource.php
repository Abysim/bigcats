<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Infolists;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
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
                        static::childrenSchema(),
                    ])
                    ->columns(1)
            ])
            ->columns(1);
    }

    public static function childrenSchema(): RepeatableEntry
    {
        return RepeatableEntry::make('featuredChildren')
            ->schema([
                Infolists\Components\ImageEntry::make('image')
                    ->width('100%')
                    ->height('auto')
                    ->extraImgAttributes(fn (Article $record): array => [
                        'alt' => $record->title,
                        'title' => $record->image_caption ?? '',
                        'class' => 'w-full aspect-[4/3] object-cover',
                        'loading' => 'lazy',
                    ])
                    ->url(fn (Article $record): string => $record->getUrl())
                    ->hiddenLabel(),
                Infolists\Components\TextEntry::make('title')
                    ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                        '<h3 class="text-base font-semibold leading-snug">' . e($state) . '</h3>'
                    ))
                    ->url(fn (Article $record): string => $record->getUrl())
                    ->hiddenLabel(),
                Infolists\Components\TextEntry::make('resume')
                    ->size(TextEntrySize::Small)
                    ->hiddenLabel(),
            ])
            ->grid(['sm' => 2])
            ->extraAttributes(['class' => 'mt-6'])
            ->hiddenLabel();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\Frontpage::route('/'),
            'view' => Pages\ViewArticle::route('/{slug1}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}'),
        ];
    }
}
