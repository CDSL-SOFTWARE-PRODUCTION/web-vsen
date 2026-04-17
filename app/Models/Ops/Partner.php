<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'segment',
        'lead_time_days',
        'reliability_note',
        'credit_limit',
        'outstanding_balance_cached',
        'max_overdue_days_cached',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'outstanding_balance_cached' => 'decimal:2',
            'max_overdue_days_cached' => 'integer',
        ];
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function cashPlanEvents(): HasMany
    {
        return $this->hasMany(CashPlanEvent::class);
    }
}
