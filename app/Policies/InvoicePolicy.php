<?php

namespace App\Policies;

use App\Models\Ops\Invoice;
use App\Models\User;
use App\Support\Ops\FilamentAccess;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, FilamentAccess::ROLES_FINANCE, true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }
}
