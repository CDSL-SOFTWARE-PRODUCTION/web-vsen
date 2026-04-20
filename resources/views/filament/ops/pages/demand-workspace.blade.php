<x-filament-panels::page>
    <x-filament::section compact>
        <div class="flex flex-wrap items-center gap-2">
            <x-filament::badge color="primary">{{ __('ops.flow.step_1_chip') }}</x-filament::badge>
            <x-filament::badge color="gray">{{ __('ops.flow.step_2_chip') }}</x-filament::badge>
            <x-filament::badge color="warning">{{ __('ops.demand_workspace.cards.gate_pipeline.title') }}</x-filament::badge>
        </div>
    </x-filament::section>

    <x-filament::section
        :heading="__('ops.demand_workspace.section.quick_start')"
        :description="__('ops.demand_workspace.section.quick_start_description')"
    >
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($primaryFlow as $entryPoint)
                <x-filament::section compact class="h-full">
                    <div class="flex h-full flex-col justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-base font-semibold">
                                    {{ $entryPoint['title'] }}
                                </h3>
                                @if (! empty($entryPoint['info']))
                                    <x-filament::icon
                                        icon="heroicon-m-information-circle"
                                        class="h-4 w-4 text-gray-500 dark:text-gray-400 focus:outline-none cursor-help"
                                        x-tooltip.raw="{{ $entryPoint['info'] }}"
                                        tabindex="0"
                                    />
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $entryPoint['description'] }}
                            </p>
                        </div>

                        <div>
                            <x-filament::button :href="$entryPoint['url']" tag="a" color="primary" size="sm">
                                {{ $entryPoint['action'] }}
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section
        :heading="__('ops.demand_workspace.section.quick_actions')"
        :description="__('ops.demand_workspace.section.quick_actions_description')"
    >
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($quickActions as $entryPoint)
                <x-filament::section compact class="h-full">
                    <div class="flex h-full flex-col justify-between gap-3">
                        <div class="space-y-2">
                            <h3 class="text-base font-semibold">
                                {{ $entryPoint['title'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $entryPoint['description'] }}
                            </p>
                        </div>

                        <div>
                            <x-filament::button :href="$entryPoint['url']" tag="a" color="gray" size="sm">
                                {{ $entryPoint['action'] }}
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
