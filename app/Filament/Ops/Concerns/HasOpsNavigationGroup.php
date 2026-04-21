<?php

namespace App\Filament\Ops\Concerns;

/**
 * Gom resource vào sidebar theo nhóm; nhãn nhóm lấy từ i18n `ops.clusters.*`.
 */
trait HasOpsNavigationGroup
{
    abstract protected static function opsNavigationClusterKey(): string;

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.'.static::opsNavigationClusterKey());
    }
}
