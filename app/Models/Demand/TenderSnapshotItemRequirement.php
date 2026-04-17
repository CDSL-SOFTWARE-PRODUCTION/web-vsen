<?php

namespace App\Models\Demand;

use App\Models\Knowledge\Requirement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderSnapshotItemRequirement extends Model
{
    protected $table = 'tender_snapshot_item_requirement';

    protected $fillable = [
        'tender_snapshot_item_id',
        'requirement_id',
    ];

    public function tenderSnapshotItem(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshotItem::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
