<?php

namespace App\Events;

use App\Models\Ops\Invoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceIssued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Invoice $invoice) {}
}
