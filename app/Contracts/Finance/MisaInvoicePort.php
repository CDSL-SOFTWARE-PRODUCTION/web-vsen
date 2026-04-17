<?php

namespace App\Contracts\Finance;

use App\Models\Ops\Invoice;

/**
 * Outbound adapter to external e-invoice (MISA). Replace binding for production.
 */
interface MisaInvoicePort
{
    /**
     * Push an issued invoice; return provider transaction id when successful.
     */
    public function pushIssued(Invoice $invoice): ?string;
}
