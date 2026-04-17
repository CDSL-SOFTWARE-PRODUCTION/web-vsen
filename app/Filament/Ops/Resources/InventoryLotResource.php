<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\Inventory;
use App\Filament\Ops\Resources\InventoryLotResource\Pages;
use App\Filament\Ops\Resources\InventoryLotResource\RelationManagers\LedgersRelationManager;
use App\Filament\Ops\Resources\InventoryLotResource\RelationManagers\ReservationsRelationManager;
use App\Models\Supply\InventoryLot;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryLotResource extends Resource
{
    protected static ?string $model = InventoryLot::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.inventory_lot.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('warehouse_code')->required()->maxLength(50)->default('DC'),
                Forms\Components\TextInput::make('available_qty')->required()->numeric()->default(0)->step(0.001),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('warehouse_code')
            ->columns([
                Tables\Columns\TextColumn::make('item_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('warehouse_code')->badge()->sortable(),
                Tables\Columns\TextColumn::make('available_qty')->numeric(decimalPlaces: 3)->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_code')
                    ->label(__('ops.resources.inventory_lot.warehouse'))
                    ->options(fn (): array => InventoryLot::query()
                        ->distinct()
                        ->orderBy('warehouse_code')
                        ->pluck('warehouse_code', 'warehouse_code')
                        ->all()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReservationsRelationManager::class,
            LedgersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryLots::route('/'),
            'create' => Pages\CreateInventoryLot::route('/create'),
            'edit' => Pages\EditInventoryLot::route('/{record}/edit'),
        ];
    }
}
