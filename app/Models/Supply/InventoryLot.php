<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'warehouse_code',
        'available_qty',
    ];

    protected function casts(): array
    {
        return [
            'warehouse_code' => 'string',
            'available_qty' => 'decimal:3',
        ];
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
