<?php

namespace App\Models\Knowledge;

use App\Models\Demand\PriceListItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanonicalProduct extends Model
{
    protected $fillable = [
        'sku',
        'raw_name',
        'abc_class',
        'medical_device_declaration_id',
        'spec_json',
        'image_urls',
    ];

    protected function casts(): array
    {
        return [
            'spec_json' => 'array',
            'image_urls' => 'array',
        ];
    }

    /**
     * @return list<string>
     */
    public function resolvedImageUrls(): array
    {
        $raw = $this->image_urls;
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
        }

        return array_values(array_unique($out));
    }

    public function medicalDeviceDeclaration(): BelongsTo
    {
        return $this->belongsTo(MedicalDeviceDeclaration::class);
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
