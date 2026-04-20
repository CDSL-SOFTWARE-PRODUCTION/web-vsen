<?php

namespace App\Models\Demand;

use App\Models\Knowledge\CanonicalProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Supply\SupplyOrderLine;
use App\Models\Supply\InventoryReservation;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'line_no',
        'lot_code',
        'name',
        'uom',
        'quantity',
        'project_location',
        'required_delivery_timeline',
        'proposed_delivery_timeline',
        'status',
        'procurement_status',
        'price_list_item_id',
        'canonical_product_id',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'line_no' => 'integer',
            'quantity' => 'decimal:3',
            'price_list_item_id' => 'integer',
            'canonical_product_id' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(PriceListItem::class);
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }

    public function supplyOrderLines(): HasMany
    {
        return $this->hasMany(SupplyOrderLine::class);
    }

    public function inventoryReservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }
}

