<?php

namespace App\Filament\Ops\Resources\MasterData\PriceListResource\RelationManagers;

use App\Filament\Ops\Support\PriceListItemFilament;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceListItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('ops.resources.price_list.items_title');
    }

    public function form(Form $form): Form
    {
        return PriceListItemFilament::itemForm($form, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return PriceListItemFilament::configureRelationTable($table, $this->getOwnerRecord());
    }
}
