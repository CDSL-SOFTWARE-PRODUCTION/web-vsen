<x-filament-panels::page>
    @php
        $selectedSupplyOrderCode = $this->selectedSupplyOrderCode();
    @endphp

    <x-filament::section
        :heading="__('ops.supply_selection_analysis.compare.section_title')"
        :description="__('ops.supply_selection_analysis.compare.section_desc')"
    >
        {{ $this->form }}
    </x-filament::section>

    <div
        class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-200"
        role="note"
    >
        {{ __('ops.supply_selection_analysis.compare.data_sources_callout') }}
    </div>

    <x-filament::section
        :heading="__('ops.supply_selection_analysis.compare.matrix_title')"
        :description="$selectedSupplyOrderCode
            ? __('ops.supply_selection_analysis.compare.selected_supply_order') . ': ' . $selectedSupplyOrderCode
            : __('ops.supply_selection_analysis.compare.pick_supply_order_first')"
    >
        @if ($this->hasComparisonCurrencyGap())
            <div class="mb-4 space-y-2">
                <div
                    class="rounded-lg border border-warning-300 bg-warning-50 p-3 text-sm text-warning-900 dark:border-warning-600 dark:bg-warning-950/40 dark:text-warning-100"
                    role="status"
                >
                    {{ __('ops.supply_selection_analysis.compare.currency_gap_warning') }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <a
                        href="{{ \App\Filament\Ops\Resources\Finance\ExchangeRateResource::getUrl('index') }}"
                        class="font-medium text-primary-600 underline decoration-primary-400/60 hover:text-primary-500 dark:text-primary-400"
                    >
                        {{ __('ops.supply_selection_analysis.compare.currency_gap_link') }}
                    </a>
                </p>
            </div>
        @endif
        <div class="overflow-x-auto">
            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-panels::page>
