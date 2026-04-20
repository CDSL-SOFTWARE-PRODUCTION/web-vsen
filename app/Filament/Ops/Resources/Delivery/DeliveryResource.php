<?php

namespace App\Filament\Ops\Resources\Delivery;

use App\Domain\Delivery\DeliveryService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Delivery\DeliveryResource\Pages;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use App\Models\Ops\DeliveryRoute;
use App\Models\Ops\Vehicle;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DeliveryResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = Delivery::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 3;

    protected static function opsNavigationClusterKey(): string
    {
        return 'delivery';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.delivery.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_DELIVERY);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->required()
                    ->options(Contract::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                        if ($state === null) {
                            return;
                        }
                        $c = Contract::query()->find($state);
                        if ($c !== null) {
                            $set('order_id', $c->order_id);
                        }
                    }),
                Forms\Components\Hidden::make('order_id'),
                Forms\Components\TextInput::make('source_warehouse_code')->maxLength(50),
                Forms\Components\TextInput::make('tracking_code')->maxLength(100),
                Forms\Components\Select::make('vehicle_id')
                    ->label(__('ops.delivery.fields.vehicle'))
                    ->options(Vehicle::query()->orderBy('code')->pluck('code', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('delivery_route_id')
                    ->label(__('ops.delivery.fields.delivery_route'))
                    ->options(DeliveryRoute::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('route_type')
                    ->options([
                        'Emergency' => 'Emergency',
                        'MilkRun' => 'MilkRun',
                        'Direct' => 'Direct',
                    ]),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'Draft' => 'Draft',
                        'Dispatched' => 'Dispatched',
                        'Delivered' => 'Delivered',
                    ])
                    ->default('Dispatched'),
                Forms\Components\TextInput::make('expected_gps_coordinates')
                    ->label(__('ops.delivery.fields.expected_gps'))
                    ->maxLength(64),
                Forms\Components\TextInput::make('gps_coordinates_actual')
                    ->label(__('ops.delivery.fields.actual_gps'))
                    ->maxLength(120),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('vehicle.code')
                    ->label(__('ops.delivery.fields.vehicle'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('deliveryRoute.name')
                    ->label(__('ops.delivery.fields.delivery_route'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('tracking_code')->limit(20),
                Tables\Columns\TextColumn::make('dispatched_at')->dateTime(),
                Tables\Columns\TextColumn::make('delivered_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('markDelivered')
                    ->label(__('ops.delivery.actions.mark_delivered'))
                    ->visible(fn (Delivery $record): bool => $record->status !== 'Delivered')
                    ->requiresConfirmation()
                    ->action(function (Delivery $record): void {
                        app(DeliveryService::class)->markDelivered($record->id, auth()->id());
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === 'Admin_PM';
    }
}
