<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'partner_id',
        'item_code',
        'name',
        'spec',
        'quantity',
        'delivery_deadline',
        'lead_time_days',
        'status',
        'docs_status',
        'cash_status',
        'is_critical',
        'line_risk_level',
    ];

    protected function casts(): array
    {
        return [
            'delivery_deadline' => 'date',
            'is_critical' => 'bool',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ExecutionIssue::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
