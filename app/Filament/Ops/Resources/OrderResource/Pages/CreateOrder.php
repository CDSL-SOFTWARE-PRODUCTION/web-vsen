<?php

namespace App\Filament\Ops\Resources\OrderResource\Pages;

use App\Filament\Ops\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        if ($user !== null && $user->legal_entity_id !== null) {
            $data['legal_entity_id'] = $user->legal_entity_id;
        }

        return $data;
    }
}
