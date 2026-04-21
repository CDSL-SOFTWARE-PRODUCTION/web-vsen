<x-filament-panels::page>
    <x-filament::section
        :heading="__('ops.admin_overview.section.portals_heading')"
        :description="__('ops.admin_overview.section.portals_description')"
    >
        <div class="flex flex-wrap gap-3">
            <x-filament::button :href="$dataStewardUrl" tag="a" color="gray" icon="heroicon-o-circle-stack">
                {{ __('ops.admin_overview.link_data_steward') }}
            </x-filament::button>
            <x-filament::button :href="$cmsUrl" tag="a" color="gray" icon="heroicon-o-newspaper">
                {{ __('ops.admin_overview.link_cms') }}
            </x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::section
        class="mt-6"
        :heading="__('ops.admin_overview.section.ops_console_heading')"
        :description="__('ops.admin_overview.section.ops_console_description')"
    >
        <x-filament::button :href="$fullDashboardUrl" tag="a" color="primary" icon="heroicon-o-chart-bar-square">
            {{ __('ops.admin_overview.link_full_dashboard') }}
        </x-filament::button>
    </x-filament::section>
</x-filament-panels::page>
