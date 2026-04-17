<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('ops:recompute-contract-risk')
    ->hourly();

Schedule::command('inventory:release-expired-reservations')
    ->daily();

Schedule::command('ops:rop-scan')
    ->dailyAt('01:15');
