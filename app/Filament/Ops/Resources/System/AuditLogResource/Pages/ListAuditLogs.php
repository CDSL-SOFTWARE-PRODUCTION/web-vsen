<?php

namespace App\Filament\Ops\Resources\System\AuditLogResource\Pages;

use App\Filament\Ops\Resources\System\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;
}

