<?php

namespace App\Filament\Ops\Clusters\Demand\Resources;

use App\Filament\Ops\Clusters\DemandCluster;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Base\OpsResource;
use App\Filament\Ops\Clusters\Demand\Resources\TenderSnapshotResource\Pages;
use App\Filament\Ops\Clusters\Demand\Resources\TenderSnapshotResource\RelationManagers\AttachmentsRelationManager;
use App\Filament\Ops\Clusters\Demand\Resources\TenderSnapshotResource\RelationManagers\BidOpeningSessionsRelationManager;
use App\Filament\Ops\Clusters\Demand\Resources\TenderSnapshotResource\RelationManagers\ItemsRelationManager;
use App\Models\Demand\TenderSnapshot;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;

class TenderSnapshotResource extends OpsResource
{
    protected static ?string $model = TenderSnapshot::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $recordTitleAttribute = 'source_notify_no';

    

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.tender_snapshot.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('ops.tender_snapshot.section_snapshot'))
                    ->description(__('ops.tender_snapshot.section_snapshot_helper'))
                    ->schema([
                        Forms\Components\TextInput::make('source_system')
                            ->required()
                            ->default('muasamcong')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('source_notify_no')
                            ->label(__('ops.tender_snapshot.fields.notify_no'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('source_plan_no')
                            ->label(__('ops.tender_snapshot.fields.plan_no'))
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\Placeholder::make('bbmt_bulk_columns')
                            ->label(__('ops.tender_snapshot.fields.bbmt_bulk_columns_label'))
                            ->content(__('ops.tender_snapshot.fields.bbmt_bulk_columns')),
                        Forms\Components\DateTimePicker::make('locked_at')
                            ->label(__('ops.tender_snapshot.fields.locked_at'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('snapshot_hash')
                            ->label(__('ops.tender_snapshot.fields.snapshot_hash'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('snapshot_version')
                            ->label(__('ops.tender_snapshot.fields.snapshot_version'))
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('source_notify_no')
                    ->label(__('ops.tender_snapshot.columns.tbmt'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_plan_no')
                    ->label(__('ops.tender_snapshot.columns.kh_lcnt'))
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('contracts_count')
                    ->counts('contracts')
                    ->label(__('ops.tender_snapshot.contracts')),
                Tables\Columns\TextColumn::make('locked_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('snapshot_hash')
                    ->label(__('ops.tender_snapshot.columns.hash'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(10)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (TenderSnapshot $record): bool => ! $record->isLocked()),
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
            AttachmentsRelationManager::class,
            BidOpeningSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenderSnapshots::route('/'),
            'create' => Pages\CreateTenderSnapshot::route('/create'),
            'view' => Pages\ViewTenderSnapshot::route('/{record}'),
            'edit' => Pages\EditTenderSnapshot::route('/{record}/edit'),
        ];
    }
}
