<?php

namespace App\Models\Supply;

use App\Models\Ops\Partner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyOrderLineSupplierQuote extends Model
{
    protected $fillable = [
        'supply_order_line_id',
        'partner_id',
        'unit_price',
        'currency_code',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'supply_order_line_id' => 'integer',
            'partner_id' => 'integer',
            'unit_price' => 'decimal:4',
            'currency_code' => 'string',
        ];
    }

    public function supplyOrderLine(): BelongsTo
    {
        return $this->belongsTo(SupplyOrderLine::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
