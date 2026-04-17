<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Placeholder for VA bank reconciliation (post-MVP). Verify signature before processing.
 */
class BankVirtualAccountWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if (! config('integrations.bank_va.webhook_enabled', false)) {
            return response('Disabled', 503);
        }

        return response()->noContent(204);
    }
}
