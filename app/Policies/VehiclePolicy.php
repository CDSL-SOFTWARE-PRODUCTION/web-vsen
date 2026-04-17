<?php

namespace App\Policies;

use App\Models\Ops\Vehicle;
use App\Models\User;
use App\Support\Ops\FilamentAccess;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return FilamentAccess::allowRoles(FilamentAccess::ROLES_DELIVERY);
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->role === 'Admin_PM';
    }
}
