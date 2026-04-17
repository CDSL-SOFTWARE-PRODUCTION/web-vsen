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
];
