<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.document_vault.declaration_docs_heading') }}</x-slot>
            @if ($declarationDocs === [])
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('ops.data_steward.document_vault.empty') }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.target') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.type') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.status') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.expiry') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($declarationDocs as $row)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2 font-medium">{{ $row['target'] }}</td>
                                    <td class="px-3 py-2">{{ $row['type'] }}</td>
                                    <td class="px-3 py-2">{{ $row['status'] }}</td>
                                    <td class="px-3 py-2">{{ $row['expiry_date'] ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        @if ($row['edit_url'] !== '')
                                            <x-filament::button :href="$row['edit_url']" tag="a" size="xs">
                                                {{ __('ops.data_steward.document_vault.open_record') }}
                                            </x-filament::button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('ops.data_steward.document_vault.product_docs_heading') }}</x-slot>
            @if ($productDocs === [])
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('ops.data_steward.document_vault.empty') }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.target') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.type') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.status') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.expiry') }}</th>
                                <th class="px-3 py-2">{{ __('ops.data_steward.document_vault.columns.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productDocs as $row)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2 font-medium">{{ $row['target'] }}</td>
                                    <td class="px-3 py-2">{{ $row['type'] }}</td>
                                    <td class="px-3 py-2">{{ $row['status'] }}</td>
                                    <td class="px-3 py-2">{{ $row['expiry_date'] ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        @if ($row['edit_url'] !== '')
                                            <x-filament::button :href="$row['edit_url']" tag="a" size="xs">
                                                {{ __('ops.data_steward.document_vault.open_record') }}
                                            </x-filament::button>
                                        @endif
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
