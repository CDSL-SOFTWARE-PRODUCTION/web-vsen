<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_order_id',
        'item_name',
        'warehouse_code',
        'quantity',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'return_order_id' => 'integer',
            'quantity' => 'decimal:3',
        ];
    }

    public function returnOrder(): BelongsTo
    {
        return $this->belongsTo(ReturnOrder::class);
    }
}
