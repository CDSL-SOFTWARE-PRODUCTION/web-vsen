<?php

namespace App\Filament\Ops\Resources\OrderResource\RelationManagers;

use App\Models\Knowledge\CanonicalProduct;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
            Forms\Components\Select::make('canonical_product_id')
                ->label(__('ops.order_items.canonical_product'))
                ->searchable()
                ->searchDebounce(400)
                ->getSearchResultsUsing(function (string $search): array {
                    $query = CanonicalProduct::query()
                        ->orderBy('raw_name')
                        ->orderBy('sku')
                        ->limit(50)
                        ->get(['id', 'raw_name', 'sku', 'spec_json']);

                    $term = trim($search);
                    if ($term !== '') {
                        $query = CanonicalProduct::query()
                            ->where('raw_name', 'like', '%'.$term.'%')
                            ->orWhere('sku', 'like', '%'.$term.'%')
                            ->orderBy('raw_name')
                            ->orderBy('sku')
                            ->limit(50)
                            ->get(['id', 'raw_name', 'sku', 'spec_json']);
                    }

                    return $query
                        ->mapWithKeys(fn (CanonicalProduct $product): array => [$product->id => self::canonicalProductLabel($product)])
                        ->all();
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    if (! is_numeric($value)) {
                        return null;
                    }

                    $product = CanonicalProduct::query()->find((int) $value, ['id', 'raw_name', 'sku', 'spec_json']);

                    return $product instanceof CanonicalProduct ? self::canonicalProductLabel($product) : null;
                })
                ->helperText(__('ops.order_items.canonical_product_helper'))
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

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('ops.order_items.title');
    }

    private static function canonicalProductLabel(CanonicalProduct $product): string
    {
        $sku = trim((string) $product->sku);
        $rawName = trim((string) $product->raw_name);
        $sizeHint = self::canonicalProductSizeHint($product);
        $nameWithSize = $sizeHint !== '' ? "{$rawName} ({$sizeHint})" : $rawName;

        if ($nameWithSize !== '' && $sku !== '') {
            return "{$nameWithSize} — {$sku}";
        }

        return $nameWithSize !== '' ? $nameWithSize : $sku;
    }

    private static function canonicalProductSizeHint(CanonicalProduct $product): string
    {
        $spec = $product->spec_json;
        if (! is_array($spec) || $spec === []) {
            return '';
        }

        $matched = [];
        foreach ($spec as $key => $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $keyText = mb_strtolower(trim((string) $key), 'UTF-8');
            $valueText = trim((string) $value);
            if ($valueText === '') {
                continue;
            }

            if (
                str_contains($keyText, 'size')
                || str_contains($keyText, 'kich')
                || str_contains($keyText, 'kích')
                || str_contains($keyText, 'quy')
                || str_contains($keyText, 'dimension')
                || str_contains($keyText, 'length')
                || str_contains($keyText, 'width')
                || str_contains($keyText, 'height')
                || str_contains($keyText, 'chieu')
                || str_contains($keyText, 'chiều')
            ) {
                $matched[] = $valueText;
            }
        }

        if ($matched === []) {
            return '';
        }

        return implode(' · ', array_slice(array_values(array_unique($matched)), 0, 2));
    }
}
