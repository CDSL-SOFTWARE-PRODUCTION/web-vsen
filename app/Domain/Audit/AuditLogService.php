<?php

namespace App\Domain\Audit;

use App\Models\System\AuditLog;

class AuditLogService
{
    public function log(
        ?int $actorUserId,
        string $entityType,
        int $entityId,
        string $action,
        array $context = []
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_user_id' => $actorUserId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'context' => $context,
        ]);
    }
}

