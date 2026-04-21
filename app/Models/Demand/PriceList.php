<?php

namespace App\Models\Demand;

use App\Models\Ops\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use HasFactory;

    public const LIST_SCOPE_SALES = 'sales';

    public const LIST_SCOPE_PROCUREMENT = 'procurement';

    public const LIST_SCOPE_BOTH = 'both';

    protected $fillable = [
        'name',
        'list_code',
        'channel',
        'list_scope',
        'status',
        'description',
        'default_currency',
        'partner_id',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'partner_id' => 'integer',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    /**
     * Active list within optional valid_from / valid_to window (for procurement & supply matrix).
     */
    public function scopeActiveAndDateValid($query)
    {
        $today = now()->toDateString();

        return $query->where('status', 'active')
            ->where(function ($q) use ($today): void {
                $q->whereNull('valid_from')
                    ->orWhereDate('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today): void {
                $q->whereNull('valid_to')
                    ->orWhereDate('valid_to', '>=', $today);
            });
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
