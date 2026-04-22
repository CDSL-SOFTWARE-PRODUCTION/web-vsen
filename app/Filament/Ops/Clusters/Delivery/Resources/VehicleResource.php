<?php

namespace App\Filament\Ops\Clusters\Delivery\Resources;

use App\Filament\Ops\Clusters\DeliveryCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Filament\Ops\Clusters\Delivery\Resources\VehicleResource\Pages;
use App\Models\Ops\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class VehicleResource extends OpsResource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $cluster = DeliveryCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.vehicle.navigation');
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', Vehicle::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(64)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('plate_number')
                ->label(__('ops.vehicle.plate'))
                ->maxLength(32),
            Forms\Components\Textarea::make('description')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('plate_number')->label(__('ops.vehicle.plate'))->placeholder('—'),
                Tables\Columns\TextColumn::make('description')->limit(40)->placeholder('—'),
                Tables\Columns\TextColumn::make('deliveries_count')->counts('deliveries')->label(__('ops.vehicle.deliveries')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }
}
