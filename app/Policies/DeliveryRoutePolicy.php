<?php

namespace App\Policies;

use App\Models\Ops\DeliveryRoute;
use App\Models\User;
use App\Support\Ops\FilamentAccess;

class DeliveryRoutePolicy
{
    public function viewAny(User $user): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_DELIVERY);
    }

    public function view(User $user, DeliveryRoute $route): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, DeliveryRoute $route): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, DeliveryRoute $route): bool
    {
        return $user->role === 'Admin_PM';
    }
}
