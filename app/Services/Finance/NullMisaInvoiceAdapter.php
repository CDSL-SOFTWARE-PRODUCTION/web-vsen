<?php

namespace App\Services\Finance;

use App\Contracts\Finance\MisaInvoicePort;
use App\Models\Ops\Invoice;

final class NullMisaInvoiceAdapter implements MisaInvoicePort
{
    public function pushIssued(Invoice $invoice): ?string
    {
        if (! config('integrations.misa.enabled', false)) {
            return null;
        }

        return 'stub-'.$invoice->id;
    }
}
