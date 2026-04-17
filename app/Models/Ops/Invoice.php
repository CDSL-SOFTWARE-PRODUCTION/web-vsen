<?php

namespace App\Models\Ops;

use App\Models\Concerns\ScopedByLegalEntity;
use App\Models\Demand\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use ScopedByLegalEntity;

    protected $fillable = [
        'legal_entity_id',
        'order_id',
        'contract_id',
        'invoice_code',
        'total_amount',
        'status',
        'payment_due_date',
        'days_overdue_cached',
        'misa_transaction_id',
        'replaced_by_invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'legal_entity_id' => 'integer',
            'order_id' => 'integer',
            'contract_id' => 'integer',
            'total_amount' => 'decimal:2',
            'payment_due_date' => 'date',
            'days_overdue_cached' => 'integer',
            'replaced_by_invoice_id' => 'integer',
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

    public function financialLedgerEntries(): HasMany
    {
        return $this->hasMany(FinancialLedgerEntry::class);
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_invoice_id');
    }
}
