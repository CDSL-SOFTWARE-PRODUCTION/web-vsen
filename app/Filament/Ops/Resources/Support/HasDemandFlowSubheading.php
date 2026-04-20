<?php

namespace App\Filament\Ops\Resources\Support;

use Illuminate\Support\HtmlString;

trait HasDemandFlowSubheading
{
    protected function demandFlowSubheading(string $chipLabel, string $hint): HtmlString
    {
        $chip = '<span class="inline-flex items-center rounded-full border border-primary-300 bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700 dark:border-primary-600/70 dark:bg-primary-900/30 dark:text-primary-200">'.$chipLabel.'</span>';

        return new HtmlString($chip.' <span class="text-sm">'.$hint.'</span>');
    }
}
