<?php

namespace App\Filament\Ops\Resources\Finance;

use App\Domain\Audit\AuditLogService;
use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Finance\PaymentMilestoneResource\Pages;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Models\Ops\Contract;
use App\Models\Ops\PaymentMilestone;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class PaymentMilestoneResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = PaymentMilestone::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static function opsNavigationClusterKey(): string
    {
        return 'finance';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.payment_milestone.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_DEMAND_EXTENDED);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('contract_id')
                    ->required()
                    ->options(Contract::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\TextInput::make('amount_planned')
                    ->required()
                    ->numeric()
                    ->prefix('VND'),
                Forms\Components\Select::make('checklist_status')
                    ->required()
                    ->options([
                        'pending' => __('ops.payment_milestone.checklist.pending'),
                        'partial' => __('ops.payment_milestone.checklist.partial'),
                        'complete' => __('ops.payment_milestone.checklist.complete'),
                    ]),
                Forms\Components\Toggle::make('payment_ready')
                    ->default(false),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date')
            ->columns([
                Tables\Columns\TextColumn::make('contract.contract_code')
                    ->label(__('ops.common.contract'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_planned')
                    ->money('VND', locale: 'vi')
                    ->summarize(Sum::make()->money('VND', locale: 'vi')),
                Tables\Columns\TextColumn::make('checklist_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('ops.payment_milestone.checklist.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\IconColumn::make('payment_ready')
                    ->boolean(),
                Tables\Columns\TextColumn::make('days_overdue_cached')
                    ->label(__('ops.payment_milestone.columns.days_overdue'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('checklist_status')
                    ->label(__('ops.common.status'))
                    ->options([
                        'pending' => __('ops.payment_milestone.checklist.pending'),
                        'partial' => __('ops.payment_milestone.checklist.partial'),
                        'complete' => __('ops.payment_milestone.checklist.complete'),
                    ]),
                Tables\Filters\Filter::make('blocked_7d')
                    ->label(__('ops.payment_milestone.filters.blocked_7d'))
                    ->query(fn ($query) => $query
                        ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                        ->where('checklist_status', '!=', 'complete')),
            ])
            ->actions([
                Tables\Actions\Action::make('markReady')
                    ->label(__('ops.payment_milestone.actions.mark_ready'))
                    ->color('success')
                    ->visible(fn (PaymentMilestone $record): bool => ! $record->payment_ready)
                    ->action(function (PaymentMilestone $record): void {
                        $hasWarning = $record->checklist_status !== 'complete';
                        $record->update([
                            'payment_ready' => true,
                            'checklist_status' => 'complete',
                        ]);

                        app(AuditLogService::class)->log(
                            auth()->id(),
                            'PaymentMilestone',
                            $record->id,
                            'MarkPaymentReady',
                            ['warn_override' => $hasWarning]
                        );

                        Notification::make()
                            ->title($hasWarning ? __('ops.gates.warn_marked_ready') : __('ops.gates.payment_ready'))
                            ->color($hasWarning ? 'warning' : 'success')
                            ->send();
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
            'index' => Pages\ListPaymentMilestones::route('/'),
            'create' => Pages\CreatePaymentMilestone::route('/create'),
            'edit' => Pages\EditPaymentMilestone::route('/{record}/edit'),
        ];
    }
}
