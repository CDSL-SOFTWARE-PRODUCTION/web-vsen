<?php

namespace App\Models\Supply;

use App\Models\Demand\OrderItem;
use App\Models\Knowledge\CanonicalProduct;
use App\Models\Ops\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_order_id',
        'order_item_id',
        'canonical_product_id',
        'supplier_partner_id',
        'supplier_suggestion_source',
        'item_name',
        'required_qty',
        'available_qty',
        'shortage_qty',
        'received_qty',
        'status',
        'planned_unit_price',
        'reference_unit_price',
        'price_deviation_pct',
        'price_deviation_flag',
    ];

    protected function casts(): array
    {
        return [
            'required_qty' => 'decimal:3',
            'available_qty' => 'decimal:3',
            'shortage_qty' => 'decimal:3',
            'received_qty' => 'decimal:3',
            'canonical_product_id' => 'integer',
            'supplier_partner_id' => 'integer',
            'supplier_suggestion_source' => 'string',
            'planned_unit_price' => 'decimal:2',
            'reference_unit_price' => 'decimal:2',
            'price_deviation_pct' => 'decimal:4',
            'price_deviation_flag' => 'boolean',
        ];
    }

    public function supplyOrder(): BelongsTo
    {
        return $this->belongsTo(SupplyOrder::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }

    public function supplierPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'supplier_partner_id');
    }
}
