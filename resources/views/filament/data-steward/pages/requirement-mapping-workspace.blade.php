<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.requirement_mapping.summary_heading') }}</x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ __('ops.data_steward.requirement_mapping.summary_body', ['count' => $missingCount]) }}
            </p>
            <div class="mt-4 flex gap-3">
                <x-filament::button :href="$requirementsUrl" tag="a" color="gray" size="sm">
                    {{ __('ops.data_steward.requirement_mapping.open_requirements') }}
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.requirement_mapping.table_heading') }}</x-slot>
            @if ($rows === [])
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('ops.data_steward.requirement_mapping.empty') }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2">{{ __('ops.data_steward.requirement_mapping.columns.sku') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.requirement_mapping.columns.name') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.requirement_mapping.columns.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2 font-medium">{{ $row['sku'] }}</td>
                                    <td class="px-3 py-2">{{ $row['name'] }}</td>
                                    <td class="px-3 py-2">
                                        <x-filament::button :href="$row['edit_url']" tag="a" size="xs">
                                            {{ __('ops.data_steward.requirement_mapping.edit_product') }}
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
