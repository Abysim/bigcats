<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'фото';

    protected static ?string $pluralModelLabel = 'фотографії';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),
                        TextInput::make('author_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
                Section::make('Flickr')
                    ->schema([
                        TextInput::make('flickr_link')
                            ->required()
                            ->url()
                            ->maxLength(1024)
                            ->columnSpanFull(),
                        TextInput::make('thumbnail_url')
                            ->required()
                            ->url()
                            ->maxLength(1024)
                            ->columnSpanFull(),
                        TextInput::make('thumbnail_width')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(1),
                        TextInput::make('thumbnail_height')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->columnSpanFull(),
                Section::make()
                    ->schema([
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->columnSpan(11),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required()
                            ->inline(false),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('tags'))
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Photo')
                    ->extraImgAttributes(fn (Photo $record): array => [
                        'alt' => e($record->name),
                        'title' => e($record->name),
                    ]),
                TextColumn::make('name')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('author_name')
                    ->searchable()
                    ->label('Author'),
                TextColumn::make('tags')
                    ->wrap()
                    ->formatStateUsing(fn (Photo $record): string => $record->tags->pluck('name')->join(', '))
                    ->searchable(query: fn ($query, string $search): mixed => $query->whereHas('tags', fn ($q) => $q->where('name', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%'))),
                ToggleColumn::make('is_published'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }
}
