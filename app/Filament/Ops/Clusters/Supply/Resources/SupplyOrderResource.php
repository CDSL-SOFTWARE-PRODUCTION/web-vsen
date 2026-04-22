<?php

namespace App\Filament\Ops\Clusters\Supply\Resources;

use App\Filament\Ops\Clusters\SupplyCluster;

use App\Domain\Supply\ApproveSupplyOrderService;
use App\Domain\Supply\MarkSupplyOrderOrderedService;
use App\Domain\Supply\ReceiveSupplyOrderService;
use App\Domain\Supply\RequestSupplyOrderApprovalService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Demand\Resources\OrderResource;
use App\Filament\Ops\Clusters\Supply\Resources\SupplyOrderResource\Pages;
use App\Filament\Ops\Clusters\Supply\Resources\SupplyOrderResource\RelationManagers\LinesRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\LegalEntity;
use App\Models\Supply\SupplyOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class SupplyOrderResource extends OpsResource
{
    protected static ?string $model = SupplyOrder::class;

    protected static ?string $cluster = SupplyCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['order.legalEntity', 'supplierPartner', 'lines.supplierPartner']);
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.supply_order.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.supply_order.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.supply_order.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('viewAny', SupplyOrder::class);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('supply_order_code')
                ->label(__('ops.supply_order.fields.supply_order_code'))
                ->hintIcon('heroicon-m-information-circle', __('ops.supply_order.fields.supplier_partner_helper'))
                ->disabled()
                ->dehydrated(false),
            Forms\Components\Textarea::make('blocked_reason')
                ->label(__('ops.supply_order.fields.blocked_reason'))
                ->rows(2)
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('supply_order_code')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('supplierPartner.name')
                    ->label(__('ops.supply_order.columns.supplier_partner'))
                    ->state(function (SupplyOrder $record): string {
                        if ($record->supplierPartner?->name !== null) {
                            return $record->supplierPartner->name;
                        }

                        $supplierNames = $record->lines
                            ->loadMissing('supplierPartner')
                            ->pluck('supplierPartner.name')
                            ->filter(fn ($name): bool => is_string($name) && $name !== '')
                            ->unique()
                            ->values();

                        if ($supplierNames->count() === 0) {
                            return '-';
                        }

                        if ($supplierNames->count() === 1) {
                            return (string) $supplierNames->first();
                        }

                        return __('ops.supply_order.columns.multiple_suppliers', ['count' => $supplierNames->count()]);
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.order_code')
                    ->label(__('ops.resources.order.navigation'))
                    ->url(fn (SupplyOrder $r): string => OrderResource::getUrl('edit', ['record' => $r->order_id])),
                Tables\Columns\TextColumn::make('order.legalEntity.name')->label(__('ops.supply_order.columns.legal_entity'))->toggleable(),
                Tables\Columns\TextColumn::make('lines_count')->counts('lines')->label(__('ops.supply_order.columns.lines')),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label(__('ops.supply_order.columns.approved_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'PendingApproval' => 'PendingApproval',
                        'Approved' => 'Approved',
                        'Ordered' => 'Ordered',
                        'PartiallyReceived' => 'PartiallyReceived',
                        'Open' => 'Open',
                        'Received' => 'Received',
                    ]),
                Tables\Filters\SelectFilter::make('legal_entity_id')
                    ->label(__('ops.supply_order.filters.legal_entity'))
                    ->options(fn (): array => LegalEntity::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas(
                            'order',
                            fn (Builder $q): Builder => $q->where('legal_entity_id', $data['value'])
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('request_approval')
                    ->label(__('ops.supply_order.actions.request_approval'))
                    ->requiresConfirmation()
                    ->visible(fn (SupplyOrder $r): bool => in_array($r->status, ['Draft', 'Open'], true))
                    ->action(function (SupplyOrder $record): void {
                        app(RequestSupplyOrderApprovalService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.supply_order.notifications.requested_approval'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label(__('ops.supply_order.actions.approve'))
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn (SupplyOrder $r): bool => $r->status === 'PendingApproval')
                    ->action(function (SupplyOrder $record): void {
                        app(ApproveSupplyOrderService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.supply_order.notifications.approved'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('mark_ordered')
                    ->label(__('ops.supply_order.actions.mark_ordered'))
                    ->requiresConfirmation()
                    ->visible(fn (SupplyOrder $r): bool => $r->status === 'Approved')
                    ->action(function (SupplyOrder $record): void {
                        app(MarkSupplyOrderOrderedService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.supply_order.notifications.ordered'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('receive')
                    ->label(__('ops.supply_order.actions.receive'))
                    ->requiresConfirmation()
                    ->visible(fn (SupplyOrder $r): bool => in_array($r->status, ['Approved', 'Ordered', 'PartiallyReceived'], true))
                    ->action(function (SupplyOrder $record): void {
                        $result = app(ReceiveSupplyOrderService::class)->handle($record->id, auth()->id());
                        Notification::make()
                            ->title(__('ops.supply_order.notifications.received'))
                            ->body(__('ops.supply_order.notifications.received_lines', ['count' => $result->receivedLinesCount]))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('open_order')
                    ->label(__('ops.supply_order.actions.open_order'))
                    ->url(fn (SupplyOrder $r): string => OrderResource::getUrl('edit', ['record' => $r->order_id])),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplyOrders::route('/'),
            'edit' => Pages\EditSupplyOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [
            LinesRelationManager::class,
        ];
    }
}
