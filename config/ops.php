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
        // C-ORD-001: DOC_HSMT, DOC_HSDT, BL_DU_THAU before AwardTender (from SubmitTender)
        'award_tender_required_docs' => env('OPS_GATE_AWARD_TENDER_DOCS', 'warn'),
        // C-ORD-003: HD_KY + credit limit (credit limit uses confirm_contract_credit_limit)
        'confirm_contract_hd_ky' => env('OPS_GATE_CONFIRM_CONTRACT_HD_KY', 'warn'),
        'confirm_contract_credit_limit' => env('OPS_GATE_CONFIRM_CONTRACT_CREDIT_LIMIT', 'warn'),
        'confirm_contract_cert_crosscheck' => env('OPS_GATE_CONFIRM_CONTRACT_CERT', 'warn'),
        'confirm_contract_negative_margin' => env('OPS_GATE_CONFIRM_CONTRACT_MARGIN', 'warn'),
        // C-ORD-004
        'close_contract_required_docs' => env('OPS_GATE_CLOSE_CONTRACT_DOCS', 'warn'),
        // C-DEL-002
        'delivery_gps_compliance' => env('OPS_GATE_DELIVERY_GPS', 'warn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | C-PR-001: warn when order line unit price deviates from PriceListItem
    |--------------------------------------------------------------------------
    */
    'price_list_deviation_warn_percent' => (float) env('OPS_PRICE_LIST_DEVIATION_WARN_PERCENT', 10),

    /*
    |--------------------------------------------------------------------------
    | Supply order approval gate
    |--------------------------------------------------------------------------
    */
    'supply_order_price_deviation_hard_percent' => (float) env('OPS_SUPPLY_PRICE_DEVIATION_HARD_PERCENT', 10),

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

    /*
    |--------------------------------------------------------------------------
    | C-DEL-002: max distance (meters) between proof GPS and expected coordinates
    |--------------------------------------------------------------------------
    */
    'delivery_gps_max_meters' => (float) env('OPS_DELIVERY_GPS_MAX_METERS', 500),

    /*
    |--------------------------------------------------------------------------
    | Reserve priority: higher number = reserved first when competing (simplified)
    |--------------------------------------------------------------------------
    */
    'reserve_priority' => [
        'critical' => (int) env('OPS_RESERVE_PRIORITY_CRITICAL', 30),
        'contract' => (int) env('OPS_RESERVE_PRIORITY_CONTRACT', 20),
        'retail' => (int) env('OPS_RESERVE_PRIORITY_RETAIL', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver API (Phase 6): X-Ops-Driver-Token header must match this value
    |--------------------------------------------------------------------------
    */
    'driver_api_token' => env('OPS_DRIVER_API_TOKEN'),
];
