<?php

namespace App\Console\Commands;

use App\Domain\Contracts\ContractRiskService;
use Illuminate\Console\Command;

class RecomputeContractRiskCommand extends Command
{
    protected $signature = 'ops:recompute-contract-risk';

    protected $description = 'Recompute contract risk cache and alert indicators';

    public function handle(ContractRiskService $riskService): int
    {
        $riskService->recomputeAll();
        $this->info('Contract risk cache updated.');

        return self::SUCCESS;
    }
}
