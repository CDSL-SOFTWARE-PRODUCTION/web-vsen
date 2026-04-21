<?php

namespace App\Models\Ops;

use App\Models\User;
use Database\Factories\Ops\FounderWorkCardFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Thin “Bitrix-style” work card surfaced on the Founder inbox (presentation layer).
 *
 * @property int $id
 * @property int $founder_user_id
 * @property string $title
 * @property string|null $summary
 * @property string|null $assignee_label
 * @property Carbon|null $due_at
 * @property string $status
 * @property string $digest_lane
 * @property list<string>|null $attachment_urls
 */
class FounderWorkCard extends Model
{
    /** @use HasFactory<FounderWorkCardFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_DONE = 'done';

    public const LANE_SIGNATURE = 'signature';

    public const LANE_REPLY = 'reply';

    public const LANE_GENERAL = 'general';

    protected $table = 'founder_work_cards';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'founder_user_id',
        'title',
        'summary',
        'assignee_label',
        'due_at',
        'status',
        'digest_lane',
        'attachment_urls',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'attachment_urls' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'founder_user_id');
    }

    /**
     * @param  Builder<FounderWorkCard>  $query
     * @return Builder<FounderWorkCard>
     */
    public function scopeOpenForFounder(Builder $query, int $userId): Builder
    {
        return $query
            ->where('founder_user_id', $userId)
            ->where('status', '!=', self::STATUS_DONE);
    }

    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_DONE) {
            return false;
        }

        return $this->due_at instanceof Carbon
            && $this->due_at->isPast();
    }

    protected static function newFactory(): FounderWorkCardFactory
    {
        return FounderWorkCardFactory::new();
    }
}
