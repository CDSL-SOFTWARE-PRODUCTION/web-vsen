<?php

namespace App\Models\Demand;

use App\Models\Concerns\ScopedByLegalEntity;
use App\Models\LegalEntity;
use App\Models\Ops\Contract;
use App\Models\Ops\Delivery;
use App\Models\Supply\SupplyOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Order extends Model
{
    use HasFactory;
    use ScopedByLegalEntity;

    private bool $allowStateMutation = false;

    protected $fillable = [
        'legal_entity_id',
        'order_code',
        'name',
        'tender_snapshot_id',
        'awarded_at',
        'confirmed_at',
        'execution_started_at',
        'fulfillment_priority',
    ];

    protected function casts(): array
    {
        return [
            'legal_entity_id' => 'integer',
            'tender_snapshot_id' => 'integer',
            'awarded_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'execution_started_at' => 'datetime',
            'fulfillment_priority' => 'string',
        ];
    }

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshot::class, 'tender_snapshot_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function salesTouchpoints(): HasMany
    {
        return $this->hasMany(SalesTouchpoint::class);
    }

    public function supplyOrders(): HasMany
    {
        return $this->hasMany(SupplyOrder::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function transitionTo(string $nextState): void
    {
        $allowedTransitions = [
            'SubmitTender' => ['AwardTender'],
            'AwardTender' => ['ConfirmContract'],
            'ConfirmContract' => ['StartExecution'],
            'StartExecution' => ['Fulfilled'],
            'Fulfilled' => ['ContractClosed'],
            'ContractClosed' => [],
            'Abandoned' => [],
        ];

        $currentState = $this->state;
        $canTransit = in_array($nextState, $allowedTransitions[$currentState] ?? [], true);
        if (! $canTransit) {
            throw new RuntimeException("Invalid order state transition from {$currentState} to {$nextState}.");
        }

        $this->state = $nextState;
        if ($nextState === 'AwardTender') {
            $this->awarded_at = now();
        }
        if ($nextState === 'ConfirmContract') {
            $this->confirmed_at = now();
        }
        if ($nextState === 'StartExecution') {
            $this->execution_started_at = now();
        }
        $this->allowStateMutation = true;
        $this->save();
        $this->allowStateMutation = false;
    }

    public function setInitialState(string $initialState): void
    {
        $this->state = $initialState;
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->state === null || $order->state === '') {
                $order->state = 'SubmitTender';
            }
        });

        static::updating(function (Order $order): void {
            if ($order->isDirty('state') && ! $order->allowStateMutation) {
                throw new RuntimeException('Order state updates must go through command transition services.');
            }
        });
    }
}
