<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts rows to the authenticated user's legal entity (non-Admin Ops roles).
 */
class LegalEntityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::hasUser()) {
            return;
        }

        $user = Auth::user();
        if ($user === null || $user->role === 'Admin_PM') {
            return;
        }

        if ($user->legal_entity_id === null) {
            $builder->whereRaw('0 = 1');

            return;
        }

        $builder->where($model->getTable().'.legal_entity_id', $user->legal_entity_id);
    }
}
