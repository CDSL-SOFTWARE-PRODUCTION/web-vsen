<?php

namespace App\Filament\Ops\Resources\UserResource\Pages;

use App\Filament\Ops\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $user = $this->getRecord();
        if (! $user instanceof User || $user->email_verified_at !== null) {
            return;
        }

        $user->forceFill(['email_verified_at' => now()])->saveQuietly();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
