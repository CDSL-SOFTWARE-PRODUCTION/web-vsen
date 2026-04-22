@php
    $columns = $this->getColumns();
    $heading = $this->getHeading();
    $description = $this->getDescription();
    $hasHeading = filled($heading);
    $hasDescription = filled($description);
    $descriptionText = $hasDescription ? html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
@endphp

<x-filament-widgets::widget class="fi-wi-stats-overview grid gap-y-4">
    @if ($hasHeading || $hasDescription)
        <div class="fi-wi-stats-overview-header grid gap-y-1">
            <div class="flex items-start gap-2">
                @if ($hasHeading)
                    <h3
                        class="fi-wi-stats-overview-header-heading min-w-0 flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white"
                    >
                        {{ $heading }}
                    </h3>
                @endif

                @if ($hasDescription)
                    <x-filament::icon
                        icon="heroicon-m-information-circle"
                        class="h-5 w-5 shrink-0 cursor-help text-gray-500 dark:text-gray-400"
                        x-tooltip.raw="{{ e($descriptionText) }}"
                        tabindex="0"
                    />
                @endif
            </div>
        </div>
    @endif

    <div
        @if ($pollingInterval = $this->getPollingInterval())
            wire:poll.{{ $pollingInterval }}
        @endif
        @class([
            'fi-wi-stats-overview-stats-ctn grid gap-6',
            'md:grid-cols-1' => $columns === 1,
            'md:grid-cols-2' => $columns === 2,
            'md:grid-cols-3' => $columns === 3,
            'md:grid-cols-2 xl:grid-cols-4' => $columns === 4,
        ])
    >
        @foreach ($this->getCachedStats() as $stat)
            {{ $stat }}
        @endforeach
    </div>
</x-filament-widgets::widget>
