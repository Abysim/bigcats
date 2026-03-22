<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class PhotoResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $model = Photo::class;

    protected static ?string $modelLabel = 'фото';

    protected static ?string $pluralModelLabel = 'фотогалерея';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_published', true);
    }
}
