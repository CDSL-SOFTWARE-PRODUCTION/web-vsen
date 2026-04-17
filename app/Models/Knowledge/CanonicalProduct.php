<?php

namespace App\Models\Knowledge;

use App\Models\Demand\PriceListItem;
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
        'image_url',
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

    public function documents(): HasMany
    {
        return $this->hasMany(CanonicalProductDocument::class);
    }

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }
}
