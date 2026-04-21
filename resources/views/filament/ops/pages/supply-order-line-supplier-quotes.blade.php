<x-filament-panels::page>
    <x-filament::section
        :heading="__('ops.supply_selection_analysis.line_quotes.catalog_vs_rfq_title')"
        :description="__('ops.supply_selection_analysis.line_quotes.catalog_vs_rfq_body')"
        class="mb-6"
    />

    {{ $this->table }}
</x-filament-panels::page>
