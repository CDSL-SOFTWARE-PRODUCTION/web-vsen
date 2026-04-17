<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\PriceListResource\Pages;
use App\Filament\Ops\Resources\PriceListResource\RelationManagers\PriceListItemsRelationManager;
use App\Models\Demand\PriceList;
use App\Models\Ops\Partner;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 13;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.price_list.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('channel')
                    ->required()
                    ->options([
                        'Hospital' => 'Hospital',
                        'Dealer' => 'Dealer',
                        'Tender' => 'Tender',
                        'Retail' => 'Retail',
                    ]),
                Forms\Components\Select::make('partner_id')
                    ->label(__('ops.resources.partner.singular'))
                    ->options(fn (): array => Partner::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\DatePicker::make('valid_from'),
                Forms\Components\DatePicker::make('valid_to'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('channel')->badge(),
                Tables\Columns\TextColumn::make('partner.name')->label(__('ops.resources.partner.singular'))->toggleable(),
                Tables\Columns\TextColumn::make('valid_from')->date()->sortable(),
                Tables\Columns\TextColumn::make('valid_to')->date(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label(__('ops.resources.price_list.lines')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PriceListItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}
