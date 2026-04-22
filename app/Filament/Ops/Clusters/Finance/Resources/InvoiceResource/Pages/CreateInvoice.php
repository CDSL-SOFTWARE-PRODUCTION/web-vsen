<?php

namespace App\Filament\Ops\Clusters\Finance\Resources\InvoiceResource\Pages;

use App\Domain\Finance\IssueInvoiceService;
use App\Filament\Ops\Clusters\Finance\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function getSubheading(): ?string
    {
        return __('ops.invoice.create.subheading');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(IssueInvoiceService::class)->handle(
            (int) $data['contract_id'],
            (float) $data['total_amount'],
            auth()->id()
        );
    }
}
