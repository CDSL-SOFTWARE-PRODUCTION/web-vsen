<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialLedgerEntry extends Model
{
    protected $table = 'financial_ledger_entries';

    protected $fillable = [
        'contract_id',
        'invoice_id',
        'partner_id',
        'related_ledger_entry_id',
        'type',
        'amount',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'contract_id' => 'integer',
            'invoice_id' => 'integer',
            'partner_id' => 'integer',
            'related_ledger_entry_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
