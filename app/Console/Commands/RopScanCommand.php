<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Placeholder for nightly ROP / ABC stock scan (system_architecture Planning slice).
 */
class RopScanCommand extends Command
{
    protected $signature = 'ops:rop-scan';

    protected $description = 'Scan stock vs ROP thresholds (stub — enable scheduling when rules are wired).';

    public function handle(): int
    {
        $this->info('ROP scan placeholder: no-op until Product/Warehouse rules are connected.');

        return self::SUCCESS;
    }
}
