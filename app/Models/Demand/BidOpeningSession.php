<?php

namespace App\Models\Demand;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BidOpeningSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_snapshot_id',
        'source_system',
        'source_notify_no',
        'source_plan_no',
        'session_version',
        'opened_at',
        'total_bidders',
        'source_url',
        'raw_payload_hash',
    ];

    protected function casts(): array
    {
        return [
            'tender_snapshot_id' => 'integer',
            'session_version' => 'integer',
            'opened_at' => 'datetime',
            'total_bidders' => 'integer',
        ];
    }

    public function tenderSnapshot(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshot::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BidOpeningLine::class);
    }

    public function awardOutcomes(): HasMany
    {
        return $this->hasMany(AwardOutcome::class);
    }
}
