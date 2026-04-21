<x-filament-panels::page>
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('ops.data_steward.dashboard.cards.declarations_without_documents') }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $triage['declarations_without_documents'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('ops.data_steward.dashboard.cards.products_without_declaration') }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $triage['products_without_declaration'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('ops.data_steward.dashboard.cards.products_without_requirements') }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $triage['products_without_requirements'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('ops.data_steward.dashboard.cards.declaration_docs_expiring_30d') }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $triage['declaration_docs_expiring_30d'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('ops.data_steward.dashboard.cards.product_docs_expiring_30d') }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $triage['product_docs_expiring_30d'] }}</div>
            </x-filament::section>
        </section>

        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.dashboard.shortcuts_heading') }}</x-slot>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($shortcuts as $shortcut)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="font-semibold">{{ $shortcut['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $shortcut['description'] }}</p>
                        <div class="mt-3">
                            <x-filament::button :href="$shortcut['url']" tag="a" size="sm">
                                {{ $shortcut['action'] }}
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.release_metrics.heading') }}</x-slot>
            <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-300">
                @foreach ($metrics as $metric)
                    <li>{{ $metric }}</li>
                @endforeach
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
