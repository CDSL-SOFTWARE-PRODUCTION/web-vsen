<?php

namespace App\Support\Ops;

/**
 * Role helpers for Filament Ops panel — see doc/ops_matrices.md (section 1).
 */
final class FilamentAccess
{
    /** @var list<string> Roles allowed into the Ops panel (see User::canAccessPanel). */
    public const ROLES_OPS_PANEL = ['Admin_PM', 'Sale', 'MuaHang', 'Kho', 'KeToan'];

    /** @var list<string> */
    public const ROLES_DEMAND_EXTENDED = ['Admin_PM', 'Sale', 'KeToan'];

    /** @var list<string> */
    public const ROLES_DELIVERY = ['Admin_PM', 'Sale', 'MuaHang', 'Kho', 'KeToan'];

    /** @var list<string> */
    public const ROLES_FINANCE = ['Admin_PM', 'KeToan'];

    /** @var list<string> */
    public const ROLES_ADMIN_ONLY = ['Admin_PM'];

    /**
     * @param  list<string>  $roles
     */
    public static function allowRoles(array $roles): bool
    {
        $user = auth()->user();

        return $user !== null && in_array($user->role, $roles, true);
    }
}
