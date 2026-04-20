<?php

namespace App\Models\Knowledge;

use App\Models\Demand\TenderSnapshotItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Requirement extends Model
{
    /** @var list<string> Requirement types attachable at SKU level in compliance matrix */
    public const SKU_ATTACHABLE_TYPES = ['ISO_13485', 'CE', 'FSC', 'Catalog'];

    protected $fillable = [
        'code',
        'type',
        'name',
        'description',
    ];

    public function canonicalProducts(): BelongsToMany
    {
        return $this->belongsToMany(CanonicalProduct::class, 'canonical_product_requirement')
            ->withTimestamps();
    }

    public function tenderSnapshotItems(): BelongsToMany
    {
        return $this->belongsToMany(TenderSnapshotItem::class, 'tender_snapshot_item_requirement')
            ->withTimestamps();
    }
}
