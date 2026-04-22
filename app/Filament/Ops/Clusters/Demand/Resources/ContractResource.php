<?php

namespace App\Filament\Ops\Clusters\Demand\Resources;

use App\Filament\Ops\Clusters\DemandCluster;

use App\Domain\Audit\AuditLogService;
use App\Domain\Execution\GateEvaluator;
use App\Domain\Execution\GateOverrideService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource\Pages;
use App\Filament\Ops\Clusters\Demand\Resources\ContractResource\RelationManagers\ItemsRelationManager;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Ops\Contract;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ContractResource extends OpsResource
{
    protected static ?string $model = Contract::class;

    protected static ?string $slug = 'demand/contracts';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = -70;

    protected static ?string $recordTitleAttribute = 'name';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.contract.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.contract.section.contract_info'))
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label(__('ops.contract.fields.order_id'))
                            ->numeric()
                            ->hintIcon('heroicon-m-information-circle', __('ops.contract.helpers.order_id'))
                            ->nullable(),
                        Forms\Components\TextInput::make('tender_snapshot_ref')
                            ->label(__('ops.contract.fields.tender_snapshot_ref'))
                            ->hintIcon('heroicon-m-information-circle', __('ops.contract.helpers.tender_snapshot_ref'))
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contract_code')
                            ->label(__('ops.contract.fields.contract_code'))
                            ->hintIcon('heroicon-m-information-circle', __('ops.contract.helpers.contract_code'))
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
                            ->default(0)
                            ->visible(fn (): bool => FilamentAccess::canSeeContractMoneySummary()),
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
                    ->label(__('ops.contract.columns.order_id'))
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
                    ->summarize(Sum::make()->money('VND', locale: 'vi'))
                    ->visible(fn (): bool => FilamentAccess::canSeeContractMoneySummary()),
                Tables\Columns\TextColumn::make('risk_level')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.common.risk.'.strtolower($state)))
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preActivateGateCheck')
                        ->label(__('ops.contract.actions.pre_activate_check'))
                        ->color('warning')
                        ->icon('heroicon-o-shield-check')
                        ->action(function (Contract $record): void {
                            $service = app(GateOverrideService::class);
                            $decision = $service->evaluate($record, 'preActivate');
                            $service->writeAudit(auth()->id(), $record, $decision);

                            Notification::make()
                                ->title($decision->hasWarnings ? __('ops.contract.notifications.gate_warnings_found') : __('ops.contract.notifications.gate_check_passed'))
                                ->body(implode("\n", $decision->warnings) ?: __('ops.contract.notifications.gate_no_warning'))
                                ->color($decision->hasWarnings ? 'warning' : 'success')
                                ->send();
                        }),
                    Tables\Actions\Action::make('preActivateGateOverride')
                        ->label(__('ops.contract.actions.override_pre_activate'))
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('override_reason')
                                ->label(__('ops.contract.override.reason_label'))
                                ->hintIcon('heroicon-m-information-circle', __('ops.contract.override.reason_helper'))
                                ->required()
                                ->maxLength(1000),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading(__('ops.contract.override.modal_pre_activate_heading'))
                        ->modalDescription(__('ops.contract.override.modal_description'))
                        ->visible(function (Contract $record): bool {
                            return app(GateEvaluator::class)->evaluatePreActivate($record)['hasWarnings'];
                        })
                        ->action(function (Contract $record, array $data): void {
                            $overrideReason = trim((string) ($data['override_reason'] ?? ''));
                            $service = app(GateOverrideService::class);
                            $decision = $service->override($record, 'preActivate', $overrideReason);
                            $service->writeAudit(auth()->id(), $record, $decision);

                            Notification::make()
                                ->title(__('ops.contract.notifications.pre_activate_override_recorded'))
                                ->body(implode("\n", $decision->warnings))
                                ->color('warning')
                                ->send();
                        }),
                    Tables\Actions\Action::make('preDeliveryGateCheck')
                        ->label(__('ops.contract.actions.pre_delivery_check'))
                        ->color('warning')
                        ->icon('heroicon-o-shield-check')
                        ->action(function (Contract $record): void {
                            $service = app(GateOverrideService::class);
                            $decision = $service->evaluate($record, 'preDelivery');
                            $service->writeAudit(auth()->id(), $record, $decision);

                            Notification::make()
                                ->title($decision->hasWarnings ? __('ops.contract.notifications.gate_warnings_found') : __('ops.contract.notifications.gate_check_passed'))
                                ->body(implode("\n", $decision->warnings) ?: __('ops.contract.notifications.gate_no_warning'))
                                ->color($decision->hasWarnings ? 'warning' : 'success')
                                ->send();
                        }),
                    Tables\Actions\Action::make('preDeliveryGateOverride')
                        ->label(__('ops.contract.actions.override_pre_delivery'))
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('override_reason')
                                ->label(__('ops.contract.override.reason_label'))
                                ->hintIcon('heroicon-m-information-circle', __('ops.contract.override.reason_helper'))
                                ->required()
                                ->maxLength(1000),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading(__('ops.contract.override.modal_pre_delivery_heading'))
                        ->modalDescription(__('ops.contract.override.modal_description'))
                        ->visible(function (Contract $record): bool {
                            return app(GateEvaluator::class)->evaluatePreDelivery($record)['hasWarnings'];
                        })
                        ->action(function (Contract $record, array $data): void {
                            $overrideReason = trim((string) ($data['override_reason'] ?? ''));
                            $service = app(GateOverrideService::class);
                            $decision = $service->override($record, 'preDelivery', $overrideReason);
                            $service->writeAudit(auth()->id(), $record, $decision);

                            Notification::make()
                                ->title(__('ops.contract.notifications.pre_delivery_override_recorded'))
                                ->body(implode("\n", $decision->warnings))
                                ->color('warning')
                                ->send();
                        }),
                    Tables\Actions\Action::make('prePaymentGate')
                        ->label(__('ops.contract.actions.pre_payment_gate'))
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
                                ->title($result['hasWarnings'] ? __('ops.contract.notifications.gate_warnings_found') : __('ops.contract.notifications.gate_check_passed'))
                                ->body(implode("\n", $result['warnings']) ?: __('ops.contract.notifications.gate_no_warning'))
                                ->color($result['hasWarnings'] ? 'warning' : 'success')
                                ->send();
                        }),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
                    ->label('')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkPreActivateGateCheck')
                        ->label(__('ops.contract.actions.bulk_pre_activate_check'))
                        ->icon('heroicon-o-shield-check')
                        ->action(function (Collection $records): void {
                            $service = app(GateOverrideService::class);
                            $passed = 0;
                            $warnings = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof Contract) {
                                    continue;
                                }
                                $decision = $service->evaluate($record, 'preActivate');
                                $service->writeAudit(auth()->id(), $record, $decision);
                                if ($decision->hasWarnings) {
                                    $warnings++;
                                } else {
                                    $passed++;
                                }
                            }

                            Notification::make()
                                ->title(__('ops.contract.notifications.bulk_gate_check_done'))
                                ->body(__('ops.contract.notifications.bulk_gate_check_summary', [
                                    'passed' => $passed,
                                    'warnings' => $warnings,
                                ]))
                                ->color($warnings > 0 ? 'warning' : 'success')
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('bulkPreDeliveryGateCheck')
                        ->label(__('ops.contract.actions.bulk_pre_delivery_check'))
                        ->icon('heroicon-o-shield-check')
                        ->action(function (Collection $records): void {
                            $service = app(GateOverrideService::class);
                            $passed = 0;
                            $warnings = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof Contract) {
                                    continue;
                                }
                                $decision = $service->evaluate($record, 'preDelivery');
                                $service->writeAudit(auth()->id(), $record, $decision);
                                if ($decision->hasWarnings) {
                                    $warnings++;
                                } else {
                                    $passed++;
                                }
                            }

                            Notification::make()
                                ->title(__('ops.contract.notifications.bulk_gate_check_done'))
                                ->body(__('ops.contract.notifications.bulk_gate_check_summary', [
                                    'passed' => $passed,
                                    'warnings' => $warnings,
                                ]))
                                ->color($warnings > 0 ? 'warning' : 'success')
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('bulkPrePaymentGateCheck')
                        ->label(__('ops.contract.actions.bulk_pre_payment_check'))
                        ->icon('heroicon-o-shield-check')
                        ->action(function (Collection $records): void {
                            $passed = 0;
                            $warnings = 0;

                            foreach ($records as $record) {
                                if (! $record instanceof Contract) {
                                    continue;
                                }
                                $result = app(GateEvaluator::class)->evaluatePrePayment($record);
                                app(AuditLogService::class)->log(
                                    auth()->id(),
                                    'Contract',
                                    $record->id,
                                    'GateCheckPrePayment',
                                    $result
                                );
                                if (($result['hasWarnings'] ?? false) === true) {
                                    $warnings++;
                                } else {
                                    $passed++;
                                }
                            }

                            Notification::make()
                                ->title(__('ops.contract.notifications.bulk_gate_check_done'))
                                ->body(__('ops.contract.notifications.bulk_gate_check_summary', [
                                    'passed' => $passed,
                                    'warnings' => $warnings,
                                ]))
                                ->color($warnings > 0 ? 'warning' : 'success')
                                ->send();
                        }),
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
