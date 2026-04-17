<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'name',
        'due_date',
        'amount_planned',
        'checklist_status',
        'payment_ready',
        'days_overdue_cached',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_planned' => 'decimal:2',
            'payment_ready' => 'bool',
            'days_overdue_cached' => 'integer',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
