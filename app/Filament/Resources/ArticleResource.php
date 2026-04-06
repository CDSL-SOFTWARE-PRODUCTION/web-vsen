<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'CMS & Content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Title'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null)
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(Article::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label(__('Excerpt'))
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('content')
                                    ->label(__('Content'))
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make(__('SEO'))
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('meta_description')
                                    ->rows(2),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Status'))
                            ->schema([
                                Forms\Components\TextInput::make('category')
                                    ->label(__('Category'))
                                    ->placeholder('e.g. News, Blog')
                                    ->maxLength(50),

                                Forms\Components\Toggle::make('is_published')
                                    ->label(__('Is Published'))
                                    ->default(false),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label(__('Published At')),

                                Forms\Components\FileUpload::make('featured_image')
                                    ->label(__('Image'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('articles'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label(__('Image'))
                    ->disk('public'),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('Category'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('Is Published'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('Published At'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('View Count'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
