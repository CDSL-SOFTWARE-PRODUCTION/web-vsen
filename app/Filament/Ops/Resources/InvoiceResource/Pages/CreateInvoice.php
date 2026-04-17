<?php

namespace App\Filament\Ops\Resources\InvoiceResource\Pages;

use App\Domain\Finance\IssueInvoiceService;
use App\Filament\Ops\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(IssueInvoiceService::class)->handle(
            (int) $data['contract_id'],
            (float) $data['total_amount'],
            auth()->id()
        );
    }
}
