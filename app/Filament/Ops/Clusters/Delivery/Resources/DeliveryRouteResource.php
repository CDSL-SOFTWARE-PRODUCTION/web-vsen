<?php

namespace App\Filament\Ops\Clusters\Delivery\Resources;

use App\Filament\Ops\Clusters\DeliveryCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Delivery\Resources\DeliveryRouteResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Ops\DeliveryRoute;
use App\Models\Ops\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class DeliveryRouteResource extends OpsResource
{
    protected static ?string $model = DeliveryRoute::class;

    protected static ?string $cluster = DeliveryCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.delivery_route.navigation');
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', DeliveryRoute::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('vehicle_id')
                ->label(__('ops.delivery_route.vehicle'))
                ->options(Vehicle::query()->orderBy('code')->pluck('code', 'id'))
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Select::make('route_type')
                ->options([
                    'Emergency' => 'Emergency',
                    'MilkRun' => 'MilkRun',
                    'Direct' => 'Direct',
                ])
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('vehicle.code')
                    ->label(__('ops.delivery_route.vehicle'))
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('route_type')->badge()->placeholder('—'),
                Tables\Columns\TextColumn::make('deliveries_count')->counts('deliveries')->label(__('ops.delivery_route.deliveries')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryRoutes::route('/'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }
}
