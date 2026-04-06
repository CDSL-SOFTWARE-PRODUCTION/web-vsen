<?php

namespace App\Filament\Cms\Resources;

use App\Filament\Cms\Resources\CmsProductResource\Pages;
use App\Filament\Cms\Resources\CmsProductResource\RelationManagers;
use App\Models\Cms\CmsProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CmsProductResource extends Resource
{
    protected static ?string $model = CmsProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make(__('Product'))
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('General'))
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make(__('Basic Information'))
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('slug')
                                                    ->required()
                                                    ->unique(CmsProduct::class, 'slug', ignoreRecord: true)
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('sku')
                                                    ->label(__('SKU'))
                                                    ->required()
                                                    ->unique(CmsProduct::class, 'sku', ignoreRecord: true)
                                                    ->maxLength(100),

                                                Forms\Components\Select::make('category_id')
                                                    ->label(__('Category'))
                                                    ->relationship('category', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),

                                                Forms\Components\TextInput::make('brand')
                                                    ->label(__('Brand'))
                                                    ->maxLength(100),

                                                Forms\Components\TextInput::make('model')
                                                    ->label(__('Model'))
                                                    ->maxLength(100),
                                            ])->columns(2),
                                    ])->columnSpan(['lg' => 2]),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make(__('Pricing & Status'))
                                            ->schema([
                                                Forms\Components\TextInput::make('price')
                                                    ->label(__('Price'))
                                                    ->numeric()
                                                    ->prefix('$'),

                                                Forms\Components\Toggle::make('show_price')
                                                    ->label(__('Show Price'))
                                                    ->default(false),

                                                Forms\Components\Select::make('status')
                                                    ->label(__('Status'))
                                                    ->options([
                                                        'new' => __('New'),
                                                        'used' => __('Used'),
                                                        'refurbished' => __('Refurbished'),
                                                    ])
                                                    ->default('new')
                                                    ->required(),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label(__('Is Active'))
                                                    ->default(true),

                                                Forms\Components\Toggle::make('is_featured')
                                                    ->label(__('Is Featured'))
                                                    ->default(false),
                                            ]),
                                    ])->columnSpan(['lg' => 1]),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make(__('Details'))
                            ->schema([
                                Forms\Components\Textarea::make('short_description')
                                    ->label(__('Short Description'))
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Description'))
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('video_url')
                                    ->label(__('Video URL'))
                                    ->url()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Images'))
                            ->schema([
                                Forms\Components\FileUpload::make('images')
                                    ->label(__('Product Images'))
                                    ->helperText(__('Kéo thả hàng loạt ảnh vào đây. Bạn có thể kéo các ảnh để thay đổi thứ tự. Ảnh đầu tiên (góc trên bên trái) sẽ tự động là ảnh đại diện.'))
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->panelLayout('grid')
                                    ->directory('products')
                                    ->disk('public')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('Specifications'))
                            ->schema([
                                Forms\Components\Repeater::make('specs')
                                    ->label(__('Specifications'))
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('spec_key')
                                            ->label(__('Name'))
                                            ->required()
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('spec_value')
                                            ->label(__('Value'))
                                            ->required(),

                                        Forms\Components\TextInput::make('spec_group')
                                            ->label(__('Group'))
                                            ->placeholder('e.g. Dimensions')
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label(__('Sort Order'))
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->orderColumn('sort_order'),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('SEO'))
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('meta_description')
                                    ->rows(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (CmsProduct $record): string => Pages\EditCmsProduct::getUrl([$record->id]),
            )
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label(__('Images'))
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->colors([
                        'success' => 'new',
                        'warning' => 'used',
                        'gray' => 'refurbished',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Is Active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('Is Featured'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListCmsProducts::route('/'),
            'create' => Pages\CreateCmsProduct::route('/create'),
            'edit' => Pages\EditCmsProduct::route('/{record}/edit'),
        ];
    }
}
