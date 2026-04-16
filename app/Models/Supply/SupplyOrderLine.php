<?php

namespace App\Models\Supply;

use App\Models\Demand\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_order_id',
        'order_item_id',
        'item_name',
        'required_qty',
        'available_qty',
        'shortage_qty',
        'received_qty',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'required_qty' => 'decimal:3',
            'available_qty' => 'decimal:3',
            'shortage_qty' => 'decimal:3',
            'received_qty' => 'decimal:3',
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
}
