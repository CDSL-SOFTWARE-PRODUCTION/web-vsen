<?php

namespace App\Models\Demand;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class TenderSnapshotAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_snapshot_id',
        'label',
        'file_path',
        'external_url',
        'mime_type',
        'file_size_bytes',
        'uploaded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'uploaded_by_user_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TenderSnapshotAttachment $attachment): void {
            if ($attachment->snapshot()->exists() && $attachment->snapshot()->first()?->isLocked()) {
                throw new RuntimeException('TenderSnapshot is locked; attachments cannot be modified.');
            }
        });

        static::deleting(function (TenderSnapshotAttachment $attachment): void {
            if ($attachment->snapshot()->exists() && $attachment->snapshot()->first()?->isLocked()) {
                throw new RuntimeException('TenderSnapshot is locked; attachments cannot be modified.');
            }
        });
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(TenderSnapshot::class, 'tender_snapshot_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}

