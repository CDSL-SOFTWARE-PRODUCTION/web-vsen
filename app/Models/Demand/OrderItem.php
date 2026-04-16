<?php

namespace App\Models\Demand;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'line_no',
        'name',
        'uom',
        'quantity',
        'status',
        'price_list_item_id',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'line_no' => 'integer',
            'quantity' => 'decimal:3',
            'price_list_item_id' => 'integer',
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
}

