<?php

namespace App\Models\Demand;

use App\Models\Knowledge\Requirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RuntimeException;

class TenderSnapshotItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_snapshot_id',
        'line_no',
        'name',
        'uom',
        'quantity_awarded',
        'tender_item_ref',
        'brand',
        'manufacturer',
        'origin_country',
        'manufacture_year',
        'spec_committed_raw',
        'project_site',
        'delivery_earliest_rule',
        'delivery_latest_rule',
        'other_requirements_raw',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'quantity_awarded' => 'decimal:3',
            'manufacture_year' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TenderSnapshotItem $item): void {
            if ($item->snapshot()->exists() && $item->snapshot()->first()?->isLocked()) {
                throw new RuntimeException('TenderSnapshot is locked; items cannot be modified.');
            }
        });

        static::deleting(function (TenderSnapshotItem $item): void {
            if ($item->snapshot()->exists() && $item->snapshot()->first()?->isLocked()) {
                throw new RuntimeException('TenderSnapshot is locked; items cannot be modified.');
            }
        });
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshot::class, 'tender_snapshot_id');
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(Requirement::class, 'tender_snapshot_item_requirement')
            ->withTimestamps();
    }
}
