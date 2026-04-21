<?php

use Tests\TestCase;

uses(TestCase::class);

it('defines only warn or hard for every ops gate', function (): void {
    $gates = config('ops.gates');
    expect($gates)->toBeArray()->not->toBeEmpty();

    foreach ($gates as $key => $mode) {
        expect($key)->toBeString();
        expect($mode)->toBeIn(['warn', 'hard']);
    }
});

it('lists expected gate keys for progressive constraint rollout', function (): void {
    $expected = [
        'confirm_fulfillment',
        'invoice_payment_milestone',
        'award_tender_required_docs',
        'confirm_contract_hd_ky',
        'confirm_contract_credit_limit',
        'confirm_contract_cert_crosscheck',
        'confirm_contract_negative_margin',
        'close_contract_required_docs',
        'delivery_gps_compliance',
    ];

    $gates = config('ops.gates');
    foreach ($expected as $key) {
        expect($gates)->toHaveKey($key);
    }
});
