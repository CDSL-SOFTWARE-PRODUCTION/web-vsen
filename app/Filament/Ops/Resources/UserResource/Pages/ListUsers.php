<?php

namespace App\Filament\Ops\Resources\UserResource\Pages;

use App\Filament\Ops\Resources\Base\Pages\OpsListRecords;
use App\Filament\Ops\Resources\UserResource;

class ListUsers extends OpsListRecords
{
    protected static string $resource = UserResource::class;
}
