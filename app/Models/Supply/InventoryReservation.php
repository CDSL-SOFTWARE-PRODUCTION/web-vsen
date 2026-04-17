<?php

namespace App\Models\Supply;

use App\Models\Demand\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_lot_id',
        'order_item_id',
        'reserved_qty',
        'status',
        'reserved_at',
        'expires_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'inventory_lot_id' => 'integer',
            'order_item_id' => 'integer',
            'reserved_qty' => 'decimal:3',
            'reserved_at' => 'datetime',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function inventoryLot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
