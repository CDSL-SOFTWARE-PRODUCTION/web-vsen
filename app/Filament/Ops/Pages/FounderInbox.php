<?php

namespace App\Filament\Ops\Pages;

use App\Models\Ops\FounderWorkCard;
use App\Support\Ops\FilamentAccess;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FounderInbox extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static string $view = 'filament.ops.pages.founder-inbox';

    protected static ?int $navigationSort = -300;

    public int $digestSignatureCount = 0;

    public int $digestReplyCount = 0;

    public int $digestOverdueCount = 0;

    /**
     * @var list<array<string, mixed>>
     */
    public array $workCards = [];

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentAccess::isFounder();
    }

    public static function getNavigationLabel(): string
    {
        return __('ops.founder_inbox.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('ops.founder_inbox.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('ops.founder_inbox.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('ops.founder_inbox.subheading');
    }

    public function mount(): void
    {
        abort_unless(FilamentAccess::isFounder(), 403);

        $userId = auth()->id();
        abort_if($userId === null, 403);

        $open = FounderWorkCard::query()
            ->openForFounder($userId)
            ->orderByRaw("CASE digest_lane WHEN 'signature' THEN 0 WHEN 'reply' THEN 1 ELSE 2 END")
            ->orderBy('due_at')
            ->get();

        $this->digestSignatureCount = $open->where('digest_lane', FounderWorkCard::LANE_SIGNATURE)->count();
        $this->digestReplyCount = $open->where('digest_lane', FounderWorkCard::LANE_REPLY)->count();
        $this->digestOverdueCount = $open->filter(fn (FounderWorkCard $c): bool => $c->isOverdue())->count();

        $this->workCards = $open->map(fn (FounderWorkCard $c): array => [
            'id' => $c->id,
            'title' => $c->title,
            'summary' => $c->summary,
            'assignee_label' => $c->assignee_label,
            'due_at' => $c->due_at?->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'digest_lane' => $c->digest_lane,
            'status' => $c->status,
            'is_overdue' => $c->isOverdue(),
            'attachment_urls' => $c->attachment_urls ?? [],
        ])->values()->all();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('digestExport')
                ->label(__('ops.founder_inbox.actions.digest_export'))
                ->url(fn (): string => route('ops.founder.digest-export'))
                ->openUrlInNewTab(),
        ];
    }
}
