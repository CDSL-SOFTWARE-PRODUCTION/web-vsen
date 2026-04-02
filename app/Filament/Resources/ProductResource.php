<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Shop');
    }

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
                                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('sku')
                                                    ->label(__('SKU'))
                                                    ->required()
                                                    ->unique(Product::class, 'sku', ignoreRecord: true)
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
                                Forms\Components\FileUpload::make('temp_images')
                                    ->label(__('Tải ảnh hàng loạt'))
                                    ->helperText(__('Chọn hoặc kéo thả nhiều ảnh cùng lúc, chúng sẽ tự động được thêm vào danh sách phía dưới.'))
                                    ->image()
                                    ->multiple()
                                    ->directory('products')
                                    ->disk('public')
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (empty($state)) return;
                                        
                                        $currentImages = $get('images') ?? [];
                                        
                                        foreach ($state as $filePath) {
                                            $currentImages[] = [
                                                'path' => $filePath,
                                                'is_primary' => false,
                                                'sort_order' => count($currentImages),
                                            ];
                                        }
                                        
                                        $set('images', $currentImages);
                                        $set('temp_images', null);
                                    }),

                                Forms\Components\Repeater::make('images')
                                    ->label(__('Images'))
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\FileUpload::make('path')
                                            ->label(__('Image'))
                                            ->image()
                                            ->disk('public')
                                            ->directory('products')
                                            ->required(),

                                        Forms\Components\TextInput::make('alt_text')
                                            ->label(__('Alt Text'))
                                            ->maxLength(255),

                                        Forms\Components\Toggle::make('is_primary')
                                            ->label(__('Is Primary'))
                                            ->default(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, $component) {
                                                if ($state) {
                                                    $statePath = $component->getStatePath();
                                                    preg_match('/images\.([^\.]+)\./', $statePath, $matches);
                                                    $currentUuid = $matches[1] ?? null;

                                                    $container = $get('../../images');
                                                    if (is_array($container)) {
                                                        foreach ($container as $uuid => $item) {
                                                            if ($currentUuid && $uuid !== $currentUuid) {
                                                                $set("../../images.{$uuid}.is_primary", false);
                                                            }
                                                        }
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->orderColumn('sort_order'),
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
                fn (Product $record): string => Pages\EditProduct::getUrl([$record->id]),
            )
            ->columns([
                Tables\Columns\ImageColumn::make('primaryImage.path')
                    ->label(__('Image'))
                    ->disk('public')
                    ->square(),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
