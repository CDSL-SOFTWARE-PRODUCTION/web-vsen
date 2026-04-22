<?php

namespace App\Filament\Ops\Clusters\MasterData\Resources\PriceListResource\RelationManagers;

use Filament\Pages\SubNavigationPosition;

use App\Filament\Ops\Clusters\MasterDataCluster;

use App\Filament\Ops\Forms\PriceListItemFilament;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PriceListItemsRelationManager extends RelationManager
{
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = MasterDataCluster::class;
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
