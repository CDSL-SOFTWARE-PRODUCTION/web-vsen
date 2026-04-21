<?php

namespace App\Models\Demand;

use App\Models\Knowledge\CanonicalProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'price_list_id',
        'canonical_product_id',
        'product_name',
        'uom',
        'supplier_sku',
        'unit_price',
        'min_qty',
        'currency',
        'notes',
        'lead_time_days',
        'inco_term',
    ];

    protected function casts(): array
    {
        return [
            'price_list_id' => 'integer',
            'canonical_product_id' => 'integer',
            'unit_price' => 'decimal:4',
            'min_qty' => 'integer',
            'lead_time_days' => 'integer',
        ];
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
