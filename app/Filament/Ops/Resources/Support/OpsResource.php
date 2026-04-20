<?php

namespace App\Filament\Ops\Resources\Support;

use App\Support\Ops\FilamentAccess;
use Filament\Resources\Resource;

/**
 * Filament Ops resource base: optional stripped sidebar for {@see FilamentAccess::isMasterDataSteward()}.
 */
abstract class OpsResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        if (! FilamentAccess::isMasterDataSteward()) {
            return parent::shouldRegisterNavigation();
        }

        return static::visibleInMasterDataStewardSidebar();
    }

    /**
     * When user role is DuLieuNen, only resources that return true appear in the sidebar.
     */
    protected static function visibleInMasterDataStewardSidebar(): bool
    {
        return false;
    }
}
