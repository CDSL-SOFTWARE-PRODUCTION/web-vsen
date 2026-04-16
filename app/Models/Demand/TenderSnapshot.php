<?php

namespace App\Models\Demand;

use App\Models\User;
use App\Models\Ops\Contract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class TenderSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_system',
        'source_notify_no',
        'source_plan_no',
        'locked_at',
        'locked_by_user_id',
        'snapshot_hash',
        'snapshot_version',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'snapshot_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (TenderSnapshot $snapshot): void {
            // Once locked, snapshot is immutable.
            if ($snapshot->getOriginal('locked_at') !== null) {
                throw new RuntimeException('TenderSnapshot is locked and cannot be modified.');
            }
        });
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TenderSnapshotItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TenderSnapshotAttachment::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function lock(?int $actorUserId = null): void
    {
        if ($this->isLocked()) {
            return;
        }

        $this->locked_at = now();
        $this->locked_by_user_id = $actorUserId ?? Auth::id();
        $this->snapshot_hash = $this->computeSnapshotHash();
        $this->snapshot_version = max(1, (int) $this->snapshot_version);

        $this->save();
    }

    private function computeSnapshotHash(): string
    {
        $payload = [
            'source_system' => $this->source_system,
            'source_notify_no' => $this->source_notify_no,
            'source_plan_no' => $this->source_plan_no,
            'items' => $this->items()
                ->orderBy('line_no')
                ->get()
                ->map(fn (TenderSnapshotItem $item) => [
                    'line_no' => $item->line_no,
                    'name' => $item->name,
                    'uom' => $item->uom,
                    'quantity_awarded' => (string) $item->quantity_awarded,
                    'tender_item_ref' => $item->tender_item_ref,
                    'brand' => $item->brand,
                    'manufacturer' => $item->manufacturer,
                    'origin_country' => $item->origin_country,
                    'manufacture_year' => $item->manufacture_year,
                    'spec_committed_raw' => $item->spec_committed_raw,
                    'project_site' => $item->project_site,
                    'delivery_earliest_rule' => $item->delivery_earliest_rule,
                    'delivery_latest_rule' => $item->delivery_latest_rule,
                    'other_requirements_raw' => $item->other_requirements_raw,
                ])
                ->all(),
            'attachments' => $this->attachments()
                ->orderBy('id')
                ->get()
                ->map(fn (TenderSnapshotAttachment $a) => [
                    'label' => $a->label,
                    'file_path' => $a->file_path,
                    'external_url' => $a->external_url,
                    'mime_type' => $a->mime_type,
                    'file_size_bytes' => $a->file_size_bytes,
                ])
                ->all(),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

