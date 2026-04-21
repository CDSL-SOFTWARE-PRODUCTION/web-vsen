<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">{{ __('ops.founder_inbox.digest.signature_heading') }}</x-slot>
            <x-slot name="description">{{ __('ops.founder_inbox.digest.signature_description') }}</x-slot>
            <p class="text-3xl font-semibold tracking-tight text-primary-600 dark:text-primary-400">
                {{ $this->digestSignatureCount }}
            </p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">{{ __('ops.founder_inbox.digest.reply_heading') }}</x-slot>
            <x-slot name="description">{{ __('ops.founder_inbox.digest.reply_description') }}</x-slot>
            <p class="text-3xl font-semibold tracking-tight text-primary-600 dark:text-primary-400">
                {{ $this->digestReplyCount }}
            </p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">{{ __('ops.founder_inbox.digest.overdue_heading') }}</x-slot>
            <x-slot name="description">{{ __('ops.founder_inbox.digest.overdue_description') }}</x-slot>
            <p class="text-3xl font-semibold tracking-tight text-danger-600 dark:text-danger-400">
                {{ $this->digestOverdueCount }}
            </p>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">{{ __('ops.founder_inbox.cards_heading') }}</x-slot>
        <x-slot name="description">{{ __('ops.founder_inbox.cards_description') }}</x-slot>

        @if (count($this->workCards) === 0)
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('ops.founder_inbox.empty') }}</p>
        @else
            <ul class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($this->workCards as $card)
                    <li class="py-4 first:pt-0">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-medium text-gray-950 dark:text-white">{{ $card['title'] }}</p>
                                @if (! empty($card['summary']))
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $card['summary'] }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if (! empty($card['assignee_label']))
                                        <span>{{ __('ops.founder_inbox.card.assignee', ['name' => $card['assignee_label']]) }}</span>
                                    @endif
                                    @if (! empty($card['due_at']))
                                        <span>{{ __('ops.founder_inbox.card.due', ['at' => $card['due_at']]) }}</span>
                                    @endif
                                    @if ($card['is_overdue'])
                                        <span class="font-medium text-danger-600 dark:text-danger-400">{{ __('ops.founder_inbox.card.overdue_badge') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('ops.founder_inbox.card.lane.'.$card['digest_lane']) }}
                            </div>
                        </div>
                        @php
                            /** @var list<string> $urls */
                            $urls = $card['attachment_urls'] ?? [];
                        @endphp
                        @if (count($urls) > 0)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($urls as $urlIndex => $url)
                                    <x-filament::button tag="a" href="{{ $url }}" size="xs" color="gray" target="_blank" rel="noopener noreferrer">
                                        {{ __('ops.founder_inbox.card.attachment_n', ['n' => $urlIndex + 1]) }}
                                    </x-filament::button>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-panels::page>
