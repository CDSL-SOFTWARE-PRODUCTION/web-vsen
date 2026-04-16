<?php

namespace App\Models\Ops;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutionIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'contract_item_id',
        'owner_user_id',
        'issue_type',
        'severity',
        'impact_flags',
        'due_at',
        'resolved_at',
        'status',
        'description',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'impact_flags' => 'array',
            'due_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ExecutionIssueUpdate::class);
    }
}
