<?php

namespace App\Filament\Ops\Resources\TenderSnapshotResource\RelationManagers;

use App\Models\Demand\TenderSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.tender_snapshot_items.title');
    }

    private function isLocked(): bool
    {
        /** @var TenderSnapshot $snapshot */
        $snapshot = $this->getOwnerRecord();

        return $snapshot->isLocked();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('line_no')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('uom')
                    ->label(__('ops.tender_snapshot_items.form.uom'))
                    ->maxLength(50),
                Forms\Components\TextInput::make('quantity_awarded')
                    ->label(__('ops.tender_snapshot_items.form.quantity_awarded'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tender_item_ref')
                    ->label(__('ops.tender_snapshot_items.form.tender_item_ref'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('brand')
                    ->label(__('ops.tender_snapshot_items.form.brand'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer')
                    ->label(__('ops.tender_snapshot_items.form.manufacturer'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('origin_country')
                    ->label(__('ops.tender_snapshot_items.form.origin_country'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacture_year')
                    ->label(__('ops.tender_snapshot_items.form.manufacture_year'))
                    ->numeric(),
                Forms\Components\Textarea::make('spec_committed_raw')
                    ->label(__('ops.tender_snapshot_items.form.spec_committed_raw'))
                    ->rows(6),
                Forms\Components\TextInput::make('project_site')
                    ->label(__('ops.tender_snapshot_items.form.project_site'))
                    ->maxLength(255),
                Forms\Components\Textarea::make('delivery_earliest_rule')
                    ->label(__('ops.tender_snapshot_items.form.delivery_earliest_rule'))
                    ->rows(2),
                Forms\Components\Textarea::make('delivery_latest_rule')
                    ->label(__('ops.tender_snapshot_items.form.delivery_latest_rule'))
                    ->rows(2),
                Forms\Components\Textarea::make('other_requirements_raw')
                    ->label(__('ops.tender_snapshot_items.form.other_requirements_raw'))
                    ->rows(2),
            ])
            ->columns(2)
            ->disabled(fn (): bool => $this->isLocked());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('line_no')
            ->columns([
                Tables\Columns\TextColumn::make('line_no')
                    ->label(__('ops.tender_snapshot_items.table.line_no'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.tender_snapshot_items.table.name'))
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('uom')
                    ->label(__('ops.tender_snapshot_items.table.uom_short'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity_awarded')
                    ->label(__('ops.tender_snapshot_items.table.qty_short'))
                    ->numeric(decimalPlaces: 3),
                Tables\Columns\TextColumn::make('tender_item_ref')
                    ->label(__('ops.tender_snapshot_items.table.tender_item_ref'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_latest_rule')
                    ->label(__('ops.tender_snapshot_items.table.delivery_latest_rule'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => !$this->isLocked()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => !$this->isLocked()),
                ]),
            ]);
    }
}
