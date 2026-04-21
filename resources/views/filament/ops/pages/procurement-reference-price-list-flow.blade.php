<x-filament-panels::page>
    <x-filament::section
        :heading="__('ops.procurement_reference_price_list.step1_heading')"
        :description="__('ops.procurement_reference_price_list.step1_desc')"
    >
        <form wire:submit.prevent="saveHeader" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap gap-3">
                <x-filament::button type="submit">
                    @if ($this->priceList)
                        {{ __('ops.procurement_reference_price_list.save_header') }}
                    @else
                        {{ __('ops.procurement_reference_price_list.save_continue') }}
                    @endif
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if ($this->priceList)
        <x-filament::section
            class="mt-8"
            :heading="__('ops.procurement_reference_price_list.step2_heading')"
            :description="__('ops.procurement_reference_price_list.step2_desc')"
        >
            <div class="overflow-x-auto">
                {{ $this->table }}
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
