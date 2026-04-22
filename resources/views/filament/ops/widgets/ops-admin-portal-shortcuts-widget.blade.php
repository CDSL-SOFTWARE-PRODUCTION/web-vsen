<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('ops.admin_overview.section.portals_heading')"
    >
        <x-slot name="headerEnd">
            <x-filament::icon
                icon="heroicon-m-information-circle"
                class="h-5 w-5 text-gray-500 dark:text-gray-400 cursor-help"
                x-tooltip.raw="{{ __('ops.admin_overview.section.portals_description') }}"
                tabindex="0"
            />
        </x-slot>

        <div class="flex flex-wrap gap-3">
            <x-filament::button :href="$dataStewardUrl" tag="a" color="gray" icon="heroicon-o-circle-stack">
                {{ __('ops.admin_overview.link_data_steward') }}
            </x-filament::button>
            <x-filament::button :href="$cmsUrl" tag="a" color="gray" icon="heroicon-o-newspaper">
                {{ __('ops.admin_overview.link_cms') }}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
