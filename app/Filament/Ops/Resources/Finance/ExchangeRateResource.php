<?php

namespace App\Filament\Ops\Resources\Finance;

use App\Filament\Ops\Concerns\HasOpsNavigationGroup;
use App\Filament\Ops\Resources\Finance\ExchangeRateResource\Pages;
use App\Filament\Ops\Resources\Support\OpsResource;
use App\Models\Ops\ExchangeRate;
use App\Support\Currency\CurrencyConverter;
use App\Support\Currency\CurrencyFormatter;
use App\Support\Currency\SupportedCurrencies;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ExchangeRateResource extends OpsResource
{
    use HasOpsNavigationGroup;

    protected static ?string $model = ExchangeRate::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 2;

    protected static function opsNavigationClusterKey(): string
    {
        return 'finance';
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.exchange_rate.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('ops.resources.exchange_rate.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ops.resources.exchange_rate.plural_model_label');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function canCreate(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_FINANCE);
    }

    public static function canEdit(Model $record): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_FINANCE);
    }

    public static function canDelete(Model $record): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_FINANCE);
    }

    public static function form(Form $form): Form
    {
        $base = CurrencyConverter::baseCurrency();

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->description(new HtmlString(e(__('ops.resources.exchange_rate.section_help'))))
                    ->schema([
                        Forms\Components\Hidden::make('base_currency')
                            ->default($base)
                            ->dehydrated(),
                        Forms\Components\Select::make('quote_currency')
                            ->label(__('ops.resources.exchange_rate.fields.quote_currency'))
                            ->options(
                                array_filter(
                                    SupportedCurrencies::selectOptions(),
                                    fn (string $label, string $code): bool => strtoupper($code) !== strtoupper($base),
                                    ARRAY_FILTER_USE_BOTH
                                )
                            )
                            ->required()
                            ->native(false)
                            ->disabled(fn (?ExchangeRate $record): bool => $record !== null)
                            ->dehydrated(),
                        Forms\Components\Placeholder::make('base_currency_display')
                            ->label(__('ops.resources.exchange_rate.fields.base_currency'))
                            ->content($base),
                        Forms\Components\TextInput::make('rate')
                            ->label(__('ops.resources.exchange_rate.fields.rate'))
                            ->numeric()
                            ->required()
                            ->minValue(0.000001)
                            ->step(0.000001)
                            ->suffix($base.' / 1 '.__('ops.resources.exchange_rate.unit_quote'))
                            ->hintIcon('heroicon-m-information-circle', __('ops.resources.exchange_rate.hints.rate')),
                        Forms\Components\DateTimePicker::make('effective_at')
                            ->label(__('ops.resources.exchange_rate.fields.effective_at'))
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->hintIcon('heroicon-m-information-circle', __('ops.resources.exchange_rate.hints.effective_at')),
                        Forms\Components\Textarea::make('note')
                            ->label(__('ops.resources.exchange_rate.fields.note'))
                            ->rows(2)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $q): Builder => $q->orderByDesc('effective_at'))
            ->columns([
                Tables\Columns\TextColumn::make('quote_currency')
                    ->label(__('ops.resources.exchange_rate.columns.quote_currency'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_currency')
                    ->label(__('ops.resources.exchange_rate.columns.base_currency'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rate')
                    ->label(__('ops.resources.exchange_rate.columns.rate'))
                    ->formatStateUsing(function ($state, ExchangeRate $record): string {
                        if (! is_numeric($state)) {
                            return '';
                        }

                        return CurrencyFormatter::formatUnitPrice((float) $state, CurrencyConverter::baseCurrency())
                            .' / 1 '.$record->quote_currency;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_at')
                    ->label(__('ops.resources.exchange_rate.columns.effective_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label(__('ops.resources.exchange_rate.columns.note'))
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('ops.resources.exchange_rate.columns.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quote_currency')
                    ->label(__('ops.resources.exchange_rate.filters.quote_currency'))
                    ->options(SupportedCurrencies::selectOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => FilamentAccess::allowRoles(FilamentAccess::ROLES_FINANCE)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExchangeRates::route('/'),
            'create' => Pages\CreateExchangeRate::route('/create'),
            'edit' => Pages\EditExchangeRate::route('/{record}/edit'),
        ];
    }
}
