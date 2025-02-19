<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
