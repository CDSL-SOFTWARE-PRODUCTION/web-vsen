<?php

namespace App\Filament\Ops\Resources\Demand\OrderResource\RelationManagers;

use App\Filament\Ops\Support\CanonicalProductSelect;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('line_no')
                ->numeric()
                ->default(function (): int {
                    $ownerRecord = $this->getOwnerRecord();
                    $maxLineNo = (int) ($ownerRecord->items()->max('line_no') ?? 0);

                    return $maxLineNo + 1;
                })
                ->dehydrated()
                ->hidden(),
            Forms\Components\TextInput::make('lot_code')
                ->label(__('ops.order_items.lot_code'))
                ->maxLength(64),
            Forms\Components\TextInput::make('name')
                ->label(__('ops.order_items.item_name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('uom')
                ->label(__('ops.order_items.uom'))
                ->maxLength(50),
            Forms\Components\TextInput::make('quantity')
                ->label(__('ops.order_items.quantity'))
                ->required()
                ->numeric(),
            Forms\Components\TextInput::make('project_location')
                ->label(__('ops.order_items.project_location'))
                ->maxLength(255),
            Forms\Components\TextInput::make('required_delivery_timeline')
                ->label(__('ops.order_items.required_delivery_timeline'))
                ->maxLength(255),
            Forms\Components\TextInput::make('proposed_delivery_timeline')
                ->label(__('ops.order_items.proposed_delivery_timeline'))
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label(__('ops.order_items.status'))
                ->options([
                    'planned' => __('ops.order_items.status_options.planned'),
                    'delivered' => __('ops.order_items.status_options.delivered'),
                    'accepted' => __('ops.order_items.status_options.accepted'),
                ])
                ->default('planned')
                ->required()
                ->native(false),
            Forms\Components\Select::make('procurement_status')
                ->label(__('ops.order_items.procurement_status'))
                ->options([
                    'pending' => __('ops.order_items.procurement_status_options.pending'),
                    'queued' => __('ops.order_items.procurement_status_options.queued'),
                    'ordered' => __('ops.order_items.procurement_status_options.ordered'),
                    'received' => __('ops.order_items.procurement_status_options.received'),
                ])
                ->default('pending')
                ->required(),
            CanonicalProductSelect::make()
                ->required(),
            Forms\Components\TextInput::make('unit_price')
                ->label(__('ops.order_items.unit_price'))
                ->numeric()
                ->visible(fn (): bool => FilamentAccess::canSeeOrderLineUnitPrice()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('line_no')
            ->columns([
                Tables\Columns\TextColumn::make('line_no')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lot_code')
                    ->label(__('ops.order_items.lot_code'))
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('ops.order_items.item_name'))
                    ->limit(45),
                Tables\Columns\TextColumn::make('uom')
                    ->label(__('ops.order_items.uom'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('ops.order_items.quantity'))
                    ->formatStateUsing(function ($state): string {
                        if (! is_numeric($state)) {
                            return (string) $state;
                        }

                        $number = (float) $state;
                        if (fmod($number, 1.0) === 0.0) {
                            return number_format($number, 0, ',', '.');
                        }

                        $formatted = number_format($number, 3, ',', '.');

                        return rtrim(rtrim($formatted, '0'), ',');
                    }),
                Tables\Columns\TextColumn::make('project_location')
                    ->label(__('ops.order_items.project_location'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(40),
                Tables\Columns\TextColumn::make('required_delivery_timeline')
                    ->label(__('ops.order_items.required_delivery_timeline'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
                Tables\Columns\TextColumn::make('proposed_delivery_timeline')
                    ->label(__('ops.order_items.proposed_delivery_timeline'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ops.order_items.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => __('ops.order_items.status_options.'.($state ?? 'planned')) !== 'ops.order_items.status_options.'.($state ?? 'planned')
                        ? __('ops.order_items.status_options.'.($state ?? 'planned'))
                        : (string) $state),
                Tables\Columns\TextColumn::make('procurement_status')
                    ->label(__('ops.order_items.procurement_status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => __('ops.order_items.procurement_status_options.'.($state ?? 'pending'))),
                Tables\Columns\TextColumn::make('canonicalProduct.sku')
                    ->label(__('ops.order_items.canonical_product'))
                    ->placeholder('-')
                    ->description(fn ($record): ?string => $record->canonicalProduct?->raw_name)
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label(__('ops.order_items.unit_price'))
                    ->money('VND', locale: 'vi')
                    ->visible(fn (): bool => FilamentAccess::canSeeOrderLineUnitPrice()),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['line_no']) && is_numeric($data['line_no'])) {
                            return $data;
                        }

                        $ownerRecord = $this->getOwnerRecord();
                        $maxLineNo = (int) ($ownerRecord->items()->max('line_no') ?? 0);
                        $data['line_no'] = $maxLineNo + 1;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.order_items.title');
    }
}
