<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'стаття';

    protected static ?string $pluralModelLabel = 'статті';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('priority')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(4294967295),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, Get $get, ?Article $record) {
                                if (empty($get('slug')) || empty($record->slug)) {
                                    $set('slug', $record->slug ?? Str::slug($state, language: config('app.locale')));
                                }
                            })
                            ->columnSpan(11),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
                Section::make('Image & Source')
                    ->columns(4)
                    ->schema([
                        FileUpload::make('image')
                            ->hiddenLabel()
                            ->directory('articles')
                            ->panelLayout('compact')
                            ->image(),
                        TextInput::make('image_caption')
                            ->maxLength(1024)
                            ->columnSpan(3),
                        TextInput::make('source_name')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('source_url')
                            ->maxLength(1024)
                            ->default(null)
                            ->url()
                            ->columnSpan(3),
                    ])
                    ->collapsible(),
                Textarea::make('resume')
                    ->autosize()
                    ->columnSpanFull(),
                TiptapEditor::make('content')
                    ->columnSpanFull()
                    ->directory('articles'),
                Section::make()
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent')
                            ->relationship(
                                'parent',
                                'title',
                                fn ($query, $record)
                                    => $record ? $query->whereNotIn('id', $record->getAllChildrenIds()) : $query
                            )
                            ->unique(modifyRuleUsing: fn (Unique $rule, Get $get, ?Article $record) =>
                                $rule->where('slug', $get('slug'))->whereNot('id', $record->id ?? 0)
                            )
                            ->rules([
                                fn (Get $get, ?Article $record) => function ($attribute, $value, $fail) use ($record) {
                                    if ($value === null && Article::frontpage()->where('id', '!=', $record?->id ?? 0)->exists()) {
                                        $fail('A frontpage article already exists. Only one root article is allowed.');
                                    }
                                },
                            ])
                            ->searchable()
                            ->columnSpan(5),
                        TextInput::make('slug')
                            ->required()
                            ->unique(modifyRuleUsing: fn (Unique $rule, Get $get, ?Article $record) =>
                                $rule->where('parent_id', $get('parent_id'))->whereNot('id', $record->id ?? 0)
                            )
                            ->maxLength(255)
                            ->columnSpan(6),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required()
                            ->inline(false),
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false)
                            ->inline(false),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('priority')
            ->columns([
                TextColumn::make('priority')
                    ->sortable(),
                ImageColumn::make('image')
                    ->extraImgAttributes(fn (Article $record): array => [
                        'alt' => addslashes($record->image_caption),
                        'title' => $record->image_caption,
                    ]),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('parent.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('is_published')
                    ->default(true),
                ToggleColumn::make('is_featured'),
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
                SelectFilter::make('parent')
                    ->relationship('parent', 'title', fn ($query) => $query->has('children')),
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
