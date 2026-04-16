<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_code',
        'name',
        'customer_name',
        'start_date',
        'end_date',
        'allocated_budget',
        'next_delivery_due_date',
        'risk_level',
        'open_items_count',
        'open_issues_count',
        'missing_docs_count',
        'cash_needed_14d',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_delivery_due_date' => 'date',
            'allocated_budget' => 'decimal:2',
            'cash_needed_14d' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ExecutionIssue::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function paymentMilestones(): HasMany
    {
        return $this->hasMany(PaymentMilestone::class);
    }

    public function cashPlanEvents(): HasMany
    {
        return $this->hasMany(CashPlanEvent::class);
    }
}
