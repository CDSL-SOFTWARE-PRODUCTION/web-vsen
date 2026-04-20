<?php

namespace App\Policies;

use App\Models\Supply\SupplyOrder;
use App\Models\User;

class SupplyOrderPolicy
{
    /**
     * See doc/guide.md Ma trận Ops — PO/inbox for Admin, MuaHang, Sale, Kho (not KeToan).
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['Admin_PM', 'MuaHang', 'Sale', 'Kho'], true);
    }

    public function view(User $user, SupplyOrder $order): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, SupplyOrder $order): bool
    {
        return in_array($user->role, ['Admin_PM', 'MuaHang'], true);
    }
}
