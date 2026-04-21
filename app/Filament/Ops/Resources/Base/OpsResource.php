<?php

namespace App\Filament\Ops\Resources\Base;

use App\Support\Ops\FilamentAccess;
use Filament\Resources\Resource;

/**
 * Base Filament resource for the Ops panel (shared typing / extension point).
 */
abstract class OpsResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        if (FilamentAccess::isFounder()) {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
