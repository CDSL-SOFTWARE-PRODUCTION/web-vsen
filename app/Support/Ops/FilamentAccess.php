<?php

namespace App\Support\Ops;

/**
 * Role helpers for Filament Ops panel — see doc/guide.md (Ma trận Ops §1).
 */
final class FilamentAccess
{
    /** @var list<string> Roles allowed into the Ops panel (see User::canAccessPanel). */
    public const ROLES_OPS_PANEL = ['Admin_PM', 'Sale', 'MuaHang', 'Kho', 'KeToan'];

    /** @var list<string> Roles allowed into the Data Steward panel. */
    public const ROLES_DATA_STEWARD_PANEL = ['Admin_PM', 'DuLieuNen'];

    public static function canAccessDataStewardPanel(): bool
    {
        return self::allowRoles(self::ROLES_DATA_STEWARD_PANEL);
    }

    public static function isAdminPm(): bool
    {
        return self::allowRoles(self::ROLES_ADMIN_ONLY);
    }

    public static function isFounder(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->role === 'Founder';
    }

    /** Data Steward primary role — hide certain Ops shortcuts in favour of the steward panel. */
    public static function isMasterDataSteward(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->role === 'DuLieuNen';
    }

    /** @var list<string> */
    public const ROLES_DEMAND_EXTENDED = ['Admin_PM', 'Sale', 'KeToan'];

    /** @var list<string> */
    public const ROLES_DELIVERY = ['Admin_PM', 'Sale', 'MuaHang', 'Kho', 'KeToan'];

    /** @var list<string> */
    public const ROLES_FINANCE = ['Admin_PM', 'KeToan'];

    /** @var list<string> */
    public const ROLES_ADMIN_ONLY = ['Admin_PM'];

    /** Unit selling price on order lines — doc/guide.md Ma trận Ops (Kho/MuaHang mù giá kênh bán). */
    public const ROLES_ORDER_LINE_UNIT_PRICE = ['Admin_PM', 'Sale', 'KeToan'];

    /** Contract budget / cash-needed style columns — hide from Kho (no commercial money). */
    public const ROLES_CONTRACT_MONEY_SUMMARY = ['Admin_PM', 'Sale', 'MuaHang', 'KeToan'];

    /**
     * @param  list<string>  $roles
     */
    public static function allowRoles(array $roles): bool
    {
        $user = auth()->user();

        return $user !== null && in_array($user->role, $roles, true);
    }

    public static function canSeeOrderLineUnitPrice(): bool
    {
        return self::allowRoles(self::ROLES_ORDER_LINE_UNIT_PRICE);
    }

    /** Allocated budget, cash gap style aggregates on Contract list/form. */
    public static function canSeeContractMoneySummary(): bool
    {
        return self::allowRoles(self::ROLES_CONTRACT_MONEY_SUMMARY);
    }
}
