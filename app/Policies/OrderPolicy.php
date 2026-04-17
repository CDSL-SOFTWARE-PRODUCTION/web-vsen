<?php

namespace App\Policies;

use App\Models\Demand\Order;
use App\Models\User;
use App\Support\Ops\FilamentAccess;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, FilamentAccess::ROLES_OPS_PANEL, true);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }
}
