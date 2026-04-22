<?php

namespace App\Filament\Ops\Clusters\System\Resources\AuditLogResource\Pages;


use App\Filament\Ops\Clusters\System\Resources\AuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditLogs extends ListRecords
{

    protected static string $resource = AuditLogResource::class;
}

