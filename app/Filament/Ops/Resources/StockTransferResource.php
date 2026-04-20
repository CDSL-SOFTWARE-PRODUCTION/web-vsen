<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\StockTransferResource\Pages;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Supply\StockTransfer;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class StockTransferResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = StockTransfer::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 2;

    protected static function opsNavigationClusterKey(): string
    {
        return 'inventory';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.stock_transfer.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transfer_code')->required()->unique(ignoreRecord: true)->maxLength(255),
                Forms\Components\TextInput::make('item_name')->required()->maxLength(255),
                Forms\Components\TextInput::make('source_warehouse_code')->required()->maxLength(50),
                Forms\Components\TextInput::make('dest_warehouse_code')->required()->maxLength(50),
                Forms\Components\TextInput::make('quantity')->required()->numeric()->step(0.001),
                Forms\Components\Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Shipped' => 'Shipped',
                        'Received' => 'Received',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->default('Pending')
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transfer_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('item_name')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('source_warehouse_code')->badge(),
                Tables\Columns\TextColumn::make('dest_warehouse_code')->badge(),
                Tables\Columns\TextColumn::make('quantity')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Pending' => 'Pending',
                    'Shipped' => 'Shipped',
                    'Received' => 'Received',
                    'Cancelled' => 'Cancelled',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
        ];
    }
}
