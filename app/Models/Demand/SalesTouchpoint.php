<?php

namespace App\Models\Demand;

use App\Models\Ops\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTouchpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'order_id',
        'activity_type',
        'occurred_at',
        'summary',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'partner_id' => 'integer',
            'order_id' => 'integer',
            'created_by_user_id' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
