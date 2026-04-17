<?php

namespace App\Filament\Ops\Resources;

use App\Filament\Ops\Clusters\MasterData;
use App\Filament\Ops\Resources\CanonicalProductResource\Pages;
use App\Models\Knowledge\CanonicalProduct;
use App\Support\Ops\FilamentAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CanonicalProductResource extends Resource
{
    protected static ?string $model = CanonicalProduct::class;

    protected static ?string $cluster = MasterData::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function getNavigationLabel(): string
    {
        return __('ops.resources.canonical_product.navigation');
    }

    public static function canViewAny(): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_OPS_PANEL);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')->required()->maxLength(64),
            Forms\Components\TextInput::make('raw_name')->required()->maxLength(512),
            Forms\Components\Select::make('abc_class')
                ->options([
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                ])
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')->searchable(),
                Tables\Columns\TextColumn::make('raw_name')->limit(40),
                Tables\Columns\TextColumn::make('abc_class')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('abc_class')->options(['A' => 'A', 'B' => 'B', 'C' => 'C']),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCanonicalProducts::route('/'),
        ];
    }
}
