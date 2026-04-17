<?php

namespace App\Models\Ops;

use App\Models\Demand\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'contract_id',
        'source_warehouse_code',
        'vehicle_id',
        'delivery_route_id',
        'route_type',
        'tracking_code',
        'gps_coordinates_actual',
        'expected_gps_coordinates',
        'status',
        'dispatched_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'contract_id' => 'integer',
            'vehicle_id' => 'integer',
            'delivery_route_id' => 'integer',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class);
    }
}
