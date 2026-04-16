<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashPlanEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'partner_id',
        'scheduled_date',
        'amount',
        'purpose',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'amount' => 'decimal:2',
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
}
