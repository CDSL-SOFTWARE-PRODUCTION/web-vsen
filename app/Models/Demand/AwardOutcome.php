<?php

namespace App\Models\Demand;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwardOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'bid_opening_session_id',
        'tender_snapshot_id',
        'source_system',
        'source_notify_no',
        'lot_code',
        'winning_bidder_identifier',
        'winning_bidder_name',
        'winning_price',
        'currency',
        'awarded_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bid_opening_session_id' => 'integer',
            'tender_snapshot_id' => 'integer',
            'winning_price' => 'decimal:2',
            'awarded_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(BidOpeningSession::class, 'bid_opening_session_id');
    }

    public function tenderSnapshot(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshot::class);
    }
}
