<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Resources\Resource;

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\Frontpage::route('/'),
            'view' => Pages\ViewArticle::route('/{slug1}/{slug2?}/{slug3?}/{slug4?}/{slug5?}/{slug6?}'),
        ];
    }
}
