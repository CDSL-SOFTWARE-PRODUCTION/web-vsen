<?php

namespace App\Models\Ops;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'contract_item_id',
        'payment_milestone_id',
        'document_group',
        'document_type',
        'status',
        'file_path',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }

    public function paymentMilestone(): BelongsTo
    {
        return $this->belongsTo(PaymentMilestone::class);
    }
}
