<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operational gate enforcement
    |--------------------------------------------------------------------------
    | warn: collect warnings on transitions; hard: throw before state change.
    |
    | confirm_fulfillment — Order transition ConfirmFulfillment (delivery + acceptance proof).
    | invoice_payment_milestone — Issue invoice: payment milestone checklist via GateEvaluator
    |   (fulfillment proof is always required regardless of this setting).
    */
    'gates' => [
        'confirm_fulfillment' => env('OPS_GATE_CONFIRM_FULFILLMENT', 'warn'),
        'invoice_payment_milestone' => env('OPS_GATE_INVOICE_PAYMENT_MILESTONE', 'warn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | C-PR-001: warn when order line unit price deviates from PriceListItem
    |--------------------------------------------------------------------------
    */
    'price_list_deviation_warn_percent' => (float) env('OPS_PRICE_LIST_DEVIATION_WARN_PERCENT', 10),

    /*
    |--------------------------------------------------------------------------
    | C-INV-002: auto-release reservation after N days (expires_at on row)
    |--------------------------------------------------------------------------
    */
    'reserve_ttl_days' => (int) env('OPS_RESERVE_TTL_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | C-INV-004: ROP scan — warn when lot available_qty below this threshold
    |--------------------------------------------------------------------------
    */
    'rop_warn_below_qty' => (float) env('OPS_ROP_WARN_BELOW_QTY', 10),
];
