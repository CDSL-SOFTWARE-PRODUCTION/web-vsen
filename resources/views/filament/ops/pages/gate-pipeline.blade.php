<x-filament-panels::page>
    <x-filament::section compact>
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <x-filament::badge color="gray">
                    {{ __('ops.gate_pipeline.total_contracts', ['count' => $totalContracts]) }}
                </x-filament::badge>
                <x-filament::badge :color="$isCompactMode ? 'warning' : 'success'">
                    {{ $isCompactMode ? __('ops.gate_pipeline.density.compact') : __('ops.gate_pipeline.density.verbose') }}
                </x-filament::badge>
            </div>
            <div>
                <x-filament::link href="/ops/demand-workspace" icon="heroicon-o-map">
                    {{ __('ops.gate_pipeline.actions.go_demand_workspace') }}
                </x-filament::link>
            </div>
        </div>
    </x-filament::section>

    <div class="grid gap-4 xl:grid-cols-3">
        @foreach (['pre_activate', 'pre_delivery', 'pre_payment'] as $stageKey)
            @php
                $stage = $stages[$stageKey];
            @endphp
            <x-filament::section>
                <x-slot name="heading">
                    {{ $stage['label'] }}
                </x-slot>
                <x-slot name="description">
                    {{ __('ops.gate_pipeline.stage_count', ['count' => $stage['count']]) }}
                </x-slot>

                <div class="space-y-3">
                    @forelse ($stage['cards'] as $card)
                        @php
                            $isWarning = $card['status'] === 'warning';
                        @endphp
                        <x-filament::section compact>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold">
                                        {{ $card['contract_code'] }}
                                    </div>
                                    <div class="truncate text-xs text-gray-600 dark:text-gray-300">
                                        {{ $card['name'] }}
                                    </div>
                                </div>
                                <x-filament::badge :color="$isWarning ? 'warning' : 'success'">
                                    {{ $isWarning ? __('ops.gate_pipeline.warning') : __('ops.gate_pipeline.passed') }}
                                </x-filament::badge>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2 text-[11px]">
                                @if (! $isCompactMode)
                                    @foreach (['pre_activate', 'pre_delivery', 'pre_payment'] as $gateKey)
                                        @php
                                            $gateStatus = $card['gates'][$gateKey];
                                        @endphp
                                        <x-filament::badge :color="$gateStatus === 'warning' ? 'danger' : 'success'" size="sm">
                                            {{ __('ops.gate_pipeline.badge.'.$gateKey) }}: {{ $gateStatus === 'warning' ? __('ops.gate_pipeline.warning_short') : __('ops.gate_pipeline.pass_short') }}
                                        </x-filament::badge>
                                    @endforeach
                                @else
                                    <x-filament::badge :color="$card['warnings_count'] > 0 ? 'danger' : 'success'" size="sm">
                                        {{ __('ops.gate_pipeline.warning_count_compact', ['count' => $card['warnings_count']]) }}
                                    </x-filament::badge>
                                @endif
                            </div>

                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                <div>{{ __('ops.gate_pipeline.customer', ['name' => $card['customer_name']]) }}</div>
                                @if ($card['next_delivery_due_date'] !== null)
                                    <div>{{ __('ops.gate_pipeline.next_delivery_due_date', ['date' => $card['next_delivery_due_date']]) }}</div>
                                @endif
                            </div>

                            @if ($card['warnings_count'] > 0 && ! $isCompactMode)
                                <div class="mt-2 border-t border-gray-200 pt-2 text-xs text-danger-700 dark:border-gray-700 dark:text-danger-200">
                                    <div class="font-medium">
                                        {{ __('ops.gate_pipeline.warning_count', ['count' => $card['warnings_count']]) }}
                                    </div>
                                    <ul class="ml-4 list-disc space-y-1">
                                        @foreach ($card['warnings'] as $warning)
                                            <li>{{ $warning }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-3">
                                <x-filament::button :href="$card['url']" tag="a" :size="$isCompactMode ? 'xs' : 'sm'" :color="$isWarning ? 'warning' : 'gray'" icon="heroicon-o-arrow-top-right-on-square">
                                    {{ __('ops.gate_pipeline.actions.open_contract') }}
                                </x-filament::button>
                            </div>
                        </x-filament::section>
                    @empty
                        <x-filament::section compact>
                            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('ops.gate_pipeline.empty_stage') }}
                            </div>
                        </x-filament::section>
                    @endforelse
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
