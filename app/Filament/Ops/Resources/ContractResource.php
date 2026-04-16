<?php

namespace App\Filament\Ops\Resources;

use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\GateEvaluator;
use App\Domain\Execution\GateOverrideService;
use App\Filament\Ops\Clusters\Demand;
use App\Filament\Ops\Resources\ContractResource\Pages;
use App\Filament\Ops\Resources\ContractResource\RelationManagers\ItemsRelationManager;
use App\Models\Ops\Contract;
use Filament\Pages\SubNavigationPosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Demand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.contract.navigation');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.contract.section.contract_info'))
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label('Order ID')
                            ->numeric()
                            ->helperText('Soft reference tới Order (core). Có thể để trống ở MVP hiện tại.')
                            ->nullable(),
                        Forms\Components\TextInput::make('tender_snapshot_ref')
                            ->label('Tender Snapshot Ref')
                            ->helperText('Ref snapshot từ muasamcong (notifyNo/planNo/hash).')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contract_code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date'),
                        Forms\Components\DatePicker::make('end_date'),
                        Forms\Components\DatePicker::make('next_delivery_due_date'),
                        Forms\Components\TextInput::make('allocated_budget')
                            ->numeric()
                            ->prefix('VND')
                            ->default(0),
                        Forms\Components\Select::make('risk_level')
                            ->required()
                            ->options([
                                'Green' => __('ops.common.risk.green'),
                                'Amber' => __('ops.common.risk.amber'),
                                'Red' => __('ops.common.risk.red'),
                            ])
                            ->default('Green'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('next_delivery_due_date')
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contract_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('next_delivery_due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('open_items_count')
                    ->label(__('ops.contract.columns.open_items'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('open_issues_count')
                    ->label(__('ops.contract.columns.open_issues'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('missing_docs_count')
                    ->label(__('ops.contract.columns.missing_docs'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('cash_needed_14d')
                    ->money('VND', locale: 'vi')
                    ->sortable()
                    ->summarize(Sum::make()->money('VND', locale: 'vi')),
                Tables\Columns\TextColumn::make('risk_level')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.common.risk.' . strtolower($state)))
                    ->color(fn (string $state): string => match ($state) {
                        'Red' => 'danger',
                        'Amber' => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('risk_level')
                    ->label(__('ops.common.risk_level'))
                    ->options([
                        'Red' => __('ops.common.risk.red'),
                        'Amber' => __('ops.common.risk.amber'),
                        'Green' => __('ops.common.risk.green'),
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->label(__('ops.contract.filters.overdue'))
                    ->query(fn (Builder $query): Builder => $query
                        ->whereDate('next_delivery_due_date', '<', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\Action::make('preActivateGateCheck')
                    ->label('Pre-activate check')
                    ->color('warning')
                    ->icon('heroicon-o-shield-check')
                    ->action(function (Contract $record): void {
                        $service = app(GateOverrideService::class);
                        $decision = $service->evaluate($record, 'preActivate');
                        $service->writeAudit(auth()->id(), $record, $decision);

                        Notification::make()
                            ->title($decision->hasWarnings ? 'Gate warnings found' : 'Gate check passed')
                            ->body(implode("\n", $decision->warnings) ?: 'No warning.')
                            ->color($decision->hasWarnings ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('preActivateGateOverride')
                    ->label('Override pre-activate')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('override_reason')
                            ->label('Override reason')
                            ->helperText('Required for audit trail.')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Override pre-activate gate')
                    ->modalDescription('Use override only when warnings are acceptable and documented.')
                    ->visible(function (Contract $record): bool {
                        return app(GateEvaluator::class)->evaluatePreActivate($record)['hasWarnings'];
                    })
                    ->action(function (Contract $record, array $data): void {
                        $overrideReason = trim((string) ($data['override_reason'] ?? ''));
                        $service = app(GateOverrideService::class);
                        $decision = $service->override($record, 'preActivate', $overrideReason);
                        $service->writeAudit(auth()->id(), $record, $decision);

                        Notification::make()
                            ->title('Pre-activate gate override recorded')
                            ->body(implode("\n", $decision->warnings))
                            ->color('warning')
                            ->send();
                    }),
                Tables\Actions\Action::make('preDeliveryGateCheck')
                    ->label('Pre-delivery check')
                    ->color('warning')
                    ->icon('heroicon-o-shield-check')
                    ->action(function (Contract $record): void {
                        $service = app(GateOverrideService::class);
                        $decision = $service->evaluate($record, 'preDelivery');
                        $service->writeAudit(auth()->id(), $record, $decision);

                        Notification::make()
                            ->title($decision->hasWarnings ? 'Gate warnings found' : 'Gate check passed')
                            ->body(implode("\n", $decision->warnings) ?: 'No warning.')
                            ->color($decision->hasWarnings ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\Action::make('preDeliveryGateOverride')
                    ->label('Override pre-delivery')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('override_reason')
                            ->label('Override reason')
                            ->helperText('Required for audit trail.')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Override pre-delivery gate')
                    ->modalDescription('Use override only when warnings are acceptable and documented.')
                    ->visible(function (Contract $record): bool {
                        return app(GateEvaluator::class)->evaluatePreDelivery($record)['hasWarnings'];
                    })
                    ->action(function (Contract $record, array $data): void {
                        $overrideReason = trim((string) ($data['override_reason'] ?? ''));
                        $service = app(GateOverrideService::class);
                        $decision = $service->override($record, 'preDelivery', $overrideReason);
                        $service->writeAudit(auth()->id(), $record, $decision);

                        Notification::make()
                            ->title('Pre-delivery gate override recorded')
                            ->body(implode("\n", $decision->warnings))
                            ->color('warning')
                            ->send();
                    }),
                Tables\Actions\Action::make('prePaymentGate')
                    ->label('Pre-payment gate')
                    ->color('warning')
                    ->action(function (Contract $record): void {
                        $result = app(GateEvaluator::class)->evaluatePrePayment($record);
                        app(AuditLogService::class)->log(
                            auth()->id(),
                            'Contract',
                            $record->id,
                            'GateCheckPrePayment',
                            $result
                        );

                        Notification::make()
                            ->title($result['hasWarnings'] ? 'Gate warnings found' : 'Gate check passed')
                            ->body(implode("\n", $result['warnings']) ?: 'No warning.')
                            ->color($result['hasWarnings'] ? 'warning' : 'success')
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
