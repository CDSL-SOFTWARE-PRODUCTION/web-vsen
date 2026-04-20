<?php

namespace App\Models\Supply;

use App\Models\Demand\Order;
use App\Models\Ops\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'supplier_partner_id',
        'supply_order_code',
        'status',
        'approval_requested_at',
        'approved_at',
        'approved_by_user_id',
        'blocked_reason',
    ];

    protected function casts(): array
    {
        return [
            'supplier_partner_id' => 'integer',
            'approval_requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'approved_by_user_id' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplyOrderLine::class);
    }

    public function supplierPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'supplier_partner_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
