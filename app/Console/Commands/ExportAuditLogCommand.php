<?php

namespace App\Console\Commands;

use App\Models\System\AuditLog;
use Illuminate\Console\Command;

class ExportAuditLogCommand extends Command
{
    protected $signature = 'ops:audit-export {--limit=100 : Max rows}';

    protected $description = 'Export recent audit log rows as JSON (stdout).';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $rows = AuditLog::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->toArray();

        $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
