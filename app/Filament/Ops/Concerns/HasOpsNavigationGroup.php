<?php

namespace App\Filament\Ops\Concerns;

/**
 * Gom resource vào sidebar theo nhóm (thay cho Filament Cluster + sub-nav ngang).
 *
 * @see \App\Filament\Ops\Clusters\* — chỉ còn dùng làm tham chiếu nhãn qua ops.clusters.*
 */
trait HasOpsNavigationGroup
{
    abstract protected static function opsNavigationClusterKey(): string;

    public static function getNavigationGroup(): ?string
    {
        return __('ops.clusters.'.static::opsNavigationClusterKey());
    }
}
