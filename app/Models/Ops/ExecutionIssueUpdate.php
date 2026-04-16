<?php

namespace App\Models\Ops;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutionIssueUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'execution_issue_id',
        'user_id',
        'status_from',
        'status_to',
        'note',
        'attachment_path',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(ExecutionIssue::class, 'execution_issue_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
