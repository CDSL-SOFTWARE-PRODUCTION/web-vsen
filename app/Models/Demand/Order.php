<?php

namespace App\Models\Demand;

use App\Models\Ops\Contract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'name',
        'state',
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

    public function transitionTo(string $nextState): void
    {
        $allowedTransitions = [
            'SubmitTender' => ['AwardTender'],
            'AwardTender' => ['ConfirmContract'],
            'ConfirmContract' => ['StartExecution'],
            'StartExecution' => [],
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
        $this->save();
    }
}

