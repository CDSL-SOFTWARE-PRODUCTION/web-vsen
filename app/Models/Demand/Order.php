<?php

namespace App\Models\Demand;

use App\Models\Ops\Contract;
use App\Models\Supply\SupplyOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Order extends Model
{
    use HasFactory;

    private bool $allowStateMutation = false;

    protected $fillable = [
        'order_code',
        'name',
        'tender_snapshot_id',
        'awarded_at',
        'confirmed_at',
        'execution_started_at',
    ];

    protected function casts(): array
    {
        return [
            'tender_snapshot_id' => 'integer',
            'awarded_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'execution_started_at' => 'datetime',
        ];
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
        static::updating(function (Order $order): void {
            if ($order->isDirty('state') && ! $order->allowStateMutation) {
                throw new RuntimeException('Order state updates must go through command transition services.');
            }
        });
    }
}

