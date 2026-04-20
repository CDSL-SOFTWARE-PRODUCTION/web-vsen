<?php

namespace App\Filament\Ops\Resources\BidOpeningSessionResource\RelationManagers;

use App\Models\Knowledge\CanonicalProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.bid_opening_lines.title');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('source_row_no')
                ->label(__('ops.bid_opening_lines.fields.source_row_no'))
                ->numeric(),
            Forms\Components\TextInput::make('lot_code')
                ->label(__('ops.bid_opening_lines.fields.lot_code'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('item_name')
                ->label(__('ops.bid_opening_lines.fields.item_name'))
                ->maxLength(255),
            Forms\Components\Select::make('canonical_product_id')
                ->label(__('ops.bid_opening_lines.fields.canonical_product'))
                ->options(fn (): array => CanonicalProduct::query()->orderBy('sku')->pluck('sku', 'id')->all())
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Select::make('mapping_status')
                ->label(__('ops.bid_opening_lines.fields.mapping_status'))
                ->options([
                    'mapped' => __('ops.bid_opening_lines.mapping_status.mapped'),
                    'unmapped' => __('ops.bid_opening_lines.mapping_status.unmapped'),
                    'conflict' => __('ops.bid_opening_lines.mapping_status.conflict'),
                ])
                ->default('unmapped')
                ->required(),
            Forms\Components\Textarea::make('mapping_note')
                ->label(__('ops.bid_opening_lines.fields.mapping_note'))
                ->rows(2),
            Forms\Components\TextInput::make('bidder_identifier')
                ->label(__('ops.bid_opening_lines.fields.bidder_identifier'))
                ->maxLength(255),
            Forms\Components\TextInput::make('bidder_name')
                ->label(__('ops.bid_opening_lines.fields.bidder_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('bid_price')
                ->label(__('ops.bid_opening_lines.fields.bid_price'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('bid_valid_days')
                ->label(__('ops.bid_opening_lines.fields.bid_valid_days'))
                ->numeric(),
            Forms\Components\TextInput::make('bid_security_value')
                ->label(__('ops.bid_opening_lines.fields.bid_security_value'))
                ->numeric(),
            Forms\Components\TextInput::make('bid_security_days')
                ->label(__('ops.bid_opening_lines.fields.bid_security_days'))
                ->numeric(),
            Forms\Components\TextInput::make('discount_rate')
                ->label(__('ops.bid_opening_lines.fields.discount_rate'))
                ->numeric(),
            Forms\Components\TextInput::make('bid_price_after_discount')
                ->label(__('ops.bid_opening_lines.fields.bid_price_after_discount'))
                ->numeric(),
            Forms\Components\TextInput::make('currency')
                ->label(__('ops.bid_opening_lines.fields.currency'))
                ->default('VND')
                ->maxLength(3),
            Forms\Components\Textarea::make('delivery_commitment')
                ->label(__('ops.bid_opening_lines.fields.delivery_commitment'))
                ->rows(2),
            Forms\Components\TextInput::make('row_fingerprint')
                ->label(__('ops.bid_opening_lines.fields.row_fingerprint'))
                ->required()
                ->maxLength(64),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('source_row_no')
            ->columns([
                Tables\Columns\TextColumn::make('source_row_no')
                    ->label(__('ops.bid_opening_lines.columns.source_row_no'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('lot_code')
                    ->label(__('ops.bid_opening_lines.columns.lot_code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('canonicalProduct.sku')
                    ->label(__('ops.bid_opening_lines.columns.canonical_product'))
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mapping_status')
                    ->label(__('ops.bid_opening_lines.columns.mapping_status'))
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            'mapped' => __('ops.bid_opening_lines.mapping_status.mapped'),
                            'conflict' => __('ops.bid_opening_lines.mapping_status.conflict'),
                            default => __('ops.bid_opening_lines.mapping_status.unmapped'),
                        };
                    })
                    ->color(function (?string $state): string {
                        return match ($state) {
                            'mapped' => 'success',
                            'conflict' => 'danger',
                            default => 'warning',
                        };
                    }),
                Tables\Columns\TextColumn::make('bidder_name')
                    ->label(__('ops.bid_opening_lines.columns.bidder_name'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('bid_price')
                    ->label(__('ops.bid_opening_lines.columns.bid_price'))
                    ->money('VND'),
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label(__('ops.bid_opening_lines.columns.discount_rate'))
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('bid_price_after_discount')
                    ->label(__('ops.bid_opening_lines.columns.bid_price_after_discount'))
                    ->money('VND')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('delivery_commitment')
                    ->label(__('ops.bid_opening_lines.columns.delivery_commitment'))
                    ->limit(32)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bid_valid_days')
                    ->label(__('ops.bid_opening_lines.columns.bid_valid_days'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bid_security_value')
                    ->label(__('ops.bid_opening_lines.columns.bid_security_value'))
                    ->money('VND')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bid_security_days')
                    ->label(__('ops.bid_opening_lines.columns.bid_security_days'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
