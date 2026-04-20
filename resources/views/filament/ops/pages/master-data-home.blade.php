@php
    use App\Filament\Ops\Resources\MasterData\CanonicalProductResource;
    use App\Filament\Ops\Resources\MasterData\LegalEntityResource;
    use App\Filament\Ops\Resources\MasterData\MedicalDeviceDeclarationResource;
    use App\Filament\Ops\Resources\MasterData\PartnerResource;
    use App\Filament\Ops\Resources\MasterData\PriceListResource;
    use App\Filament\Ops\Resources\MasterData\RequirementResource;
@endphp

<x-filament-panels::page>
    <div class="grid gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('ops.master_data_home.section_dossiers') }}
            </x-slot>
            <x-slot name="description">
                {{ __('ops.master_data_home.section_dossiers_desc') }}
            </x-slot>
            <ul class="list-inside list-disc space-y-2 text-sm text-gray-700 dark:text-gray-200">
                <li>
                    <a href="{{ MedicalDeviceDeclarationResource::getUrl('create') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_declaration_create') }}
                    </a>
                </li>
                <li>
                    <a href="{{ MedicalDeviceDeclarationResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_declaration_list') }}
                    </a>
                </li>
                <li>
                    <a href="{{ CanonicalProductResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_canonical_products') }}
                    </a>
                </li>
            </ul>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                {{ __('ops.master_data_home.section_catalog') }}
            </x-slot>
            <x-slot name="description">
                {{ __('ops.master_data_home.section_catalog_desc') }}
            </x-slot>
            <ul class="list-inside list-disc space-y-2 text-sm text-gray-700 dark:text-gray-200">
                <li>
                    <a href="{{ PartnerResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_partners') }}
                    </a>
                </li>
                <li>
                    <a href="{{ LegalEntityResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_legal_entities') }}
                    </a>
                </li>
                <li>
                    <a href="{{ PriceListResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_price_lists') }}
                    </a>
                </li>
                <li>
                    <a href="{{ RequirementResource::getUrl('index') }}" class="text-primary-600 hover:underline dark:text-primary-400">
                        {{ __('ops.master_data_home.link_requirements') }}
                    </a>
                </li>
            </ul>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            {{ __('ops.master_data_home.section_flow') }}
        </x-slot>
        <ol class="list-inside list-decimal space-y-2 text-sm text-gray-700 dark:text-gray-200">
            <li>{{ __('ops.master_data_home.flow_step_1') }}</li>
            <li>{{ __('ops.master_data_home.flow_step_2') }}</li>
            <li>{{ __('ops.master_data_home.flow_step_3') }}</li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
