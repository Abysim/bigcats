<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'новина';

    protected static ?string $pluralModelLabel = 'новини';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('date')
                            ->required(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                            ->columnSpan(6),
                        ])
                    ->columns(7)
                    ->columnSpanFull(),
                Section::make('Image & Source')
                    ->columns(4)
                    ->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->directory('news')
                            ->panelLayout('compact')
                            ->image(),
                        TextInput::make('image_caption')
                            ->maxLength(255)
                            ->columnSpan(3),
                        TextInput::make('source_name')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('source_url')
                            ->maxLength(1024)
                            ->default(null)
                            ->columnSpan(3),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDirectory('news')
                    ->getUploadedAttachmentUrlUsing(fn ($file) => '/storage/' . $file),
                Section::make()
                    ->schema([
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->columnSpan(11),
                        Toggle::make('is_original')
                            ->label('Original')
                            ->required()
                            ->inline(false),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(11),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required()
                            ->inline(false),
                    ])
                    ->columns(12)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->extraImgAttributes(fn (News $record): array => [
                        'alt' => $record->image_caption,
                        'title' => $record->image_caption,
                    ]),
                TextColumn::make('date')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('source_name')
                    ->formatStateUsing(fn (string $state, News $record): HtmlString => new HtmlString(
                        $record->source_url
                            ? "<a title=\"$record->source_url\" href=\"$record->source_url\" target=\"_blank\">$state</a>"
                            : $state
                    ))
                    ->searchable()
                    ->label('Source'),
                TextColumn::make('tags')
                    ->formatStateUsing(fn (News $record): string => $record->tags->pluck('name')->join(', '))
                    ->searchable(),
                ToggleColumn::make('is_original'),
                ToggleColumn::make('is_published'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
