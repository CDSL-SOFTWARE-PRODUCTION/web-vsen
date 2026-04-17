<?php

namespace App\Models\Concerns;

use App\Models\LegalEntity;
use App\Models\Scopes\LegalEntityScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ScopedByLegalEntity
{
    public static function bootScopedByLegalEntity(): void
    {
        static::addGlobalScope(new LegalEntityScope);
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }
}
