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

    <x-filament::section
        :heading="__('ops.supply_selection_analysis.compare.matrix_title')"
        :description="$selectedSupplyOrderCode
            ? __('ops.supply_selection_analysis.compare.selected_supply_order') . ': ' . $selectedSupplyOrderCode
            : __('ops.supply_selection_analysis.compare.pick_supply_order_first')"
    >
        <div class="overflow-x-auto">
            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-panels::page>
