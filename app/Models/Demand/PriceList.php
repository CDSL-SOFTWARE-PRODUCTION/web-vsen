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

    protected $fillable = [
        'name',
        'channel',
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

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
