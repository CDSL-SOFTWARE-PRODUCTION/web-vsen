<?php

namespace App\Filament\Ops\Clusters\Inventory\Resources;

use App\Filament\Ops\Clusters\InventoryCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Inventory\Resources\InventoryReservationResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Filament\Ops\Clusters\Demand\Resources\OrderResource;
use App\Models\Demand\OrderItem;
use App\Models\Supply\InventoryLot;
use App\Models\Supply\InventoryReservation;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryReservationResource extends OpsResource
{
    protected static ?string $model = InventoryReservation::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 4;

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.inventory_reservation.navigation');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['inventoryLot', 'orderItem.order']);
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('inventory_lot_id')
                    ->label(__('ops.resources.inventory_lot.navigation'))
                    ->options(fn (): array => InventoryLot::query()
                        ->orderBy('warehouse_code')
                        ->orderBy('item_name')
                        ->get()
                        ->mapWithKeys(function (InventoryLot $lot): array {
                            $lotPart = $lot->lot_code ? ' · '.$lot->lot_code : '';
                            $exp = $lot->expiry_date ? ' · HSD '.$lot->expiry_date->format('d/m/Y') : '';

                            return [
                                $lot->id => $lot->warehouse_code.' · '.$lot->item_name.$lotPart.$exp.' (#'.$lot->id.')',
                            ];
                        })
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('order_item_id')
                    ->label(__('ops.resources.inventory_reservation.order_item'))
                    ->options(fn (): array => OrderItem::query()
                        ->with('order')
                        ->orderByDesc('id')
                        ->limit(1000)
                        ->get()
                        ->mapWithKeys(fn (OrderItem $i): array => [
                            $i->id => ($i->order?->order_code ?? '—').' · '.str($i->name)->limit(40).' (#'.$i->id.')',
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('reserved_qty')->required()->numeric()->minValue(0)->step(0.001),
                Forms\Components\Select::make('status')
                    ->options([
                        'Reserved' => 'Reserved',
                        'Released' => 'Released',
                        'Expired' => 'Expired',
                    ])
                    ->default('Reserved')
                    ->required(),
                Forms\Components\DateTimePicker::make('reserved_at'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\DateTimePicker::make('released_at'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inventoryLot.warehouse_code')->label(__('ops.resources.inventory_lot.warehouse'))->badge(),
                Tables\Columns\TextColumn::make('inventoryLot.item_name')->limit(32)->searchable(),
                Tables\Columns\TextColumn::make('orderItem.order.order_code')
                    ->label(__('ops.resources.order.navigation'))
                    ->url(fn (InventoryReservation $r): ?string => $r->order_item_id && $r->orderItem?->order_id
                        ? OrderResource::getUrl('edit', ['record' => $r->orderItem->order_id])
                        : null)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('reserved_qty')->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'Reserved' => 'Reserved',
                    'Released' => 'Released',
                    'Expired' => 'Expired',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryReservations::route('/'),
        ];
    }
}
