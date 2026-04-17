<?php

namespace App\Models\Knowledge;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanonicalProduct extends Model
{
    protected $fillable = [
        'sku',
        'raw_name',
        'abc_class',
        'spec_json',
    ];

    protected function casts(): array
    {
        return [
            'spec_json' => 'array',
        ];
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(ProductAlias::class);
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'canonical_product_requirement')
            ->withTimestamps();
    }
}
