<?php

namespace App\Models\Supply;

use App\Models\Knowledge\CanonicalProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'canonical_product_id',
        'item_name',
        'warehouse_code',
        'lot_code',
        'supplier_ref',
        'mfg_date',
        'expiry_date',
        'available_qty',
    ];

    protected function casts(): array
    {
        return [
            'canonical_product_id' => 'integer',
            'warehouse_code' => 'string',
            'available_qty' => 'decimal:3',
            'mfg_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function canonicalProduct(): BelongsTo
    {
        return $this->belongsTo(CanonicalProduct::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(InventoryLedger::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }
}
