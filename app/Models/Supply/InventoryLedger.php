<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_lot_id',
        'supply_order_id',
        'supply_order_line_id',
        'item_name',
        'action',
        'qty_change',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'inventory_lot_id' => 'integer',
            'supply_order_id' => 'integer',
            'supply_order_line_id' => 'integer',
            'qty_change' => 'decimal:3',
            'balance_after' => 'decimal:3',
        ];
    }

    public function inventoryLot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class);
    }

    public function supplyOrder(): BelongsTo
    {
        return $this->belongsTo(SupplyOrder::class);
    }

    public function supplyOrderLine(): BelongsTo
    {
        return $this->belongsTo(SupplyOrderLine::class);
    }
}
