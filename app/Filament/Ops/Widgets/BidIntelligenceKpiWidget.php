<?php

namespace App\Filament\Ops\Widgets;

use App\Models\Demand\AwardOutcome;
use App\Models\Demand\BidOpeningLine;
use App\Models\Demand\BidOpeningSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BidIntelligenceKpiWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 40;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return __('ops.dashboard.bid_intelligence.heading');
    }

    public function getDescription(): ?string
    {
        return __('ops.dashboard.bid_intelligence.description');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $last30days = now()->subDays(30);
        $totalSessions = BidOpeningSession::query()->count();
        $totalLines = BidOpeningLine::query()->count();
        $avgBidders = (float) BidOpeningSession::query()->avg('total_bidders');
        $awardCoverage = AwardOutcome::query()
            ->where('created_at', '>=', $last30days)
            ->count();

        return [
            Stat::make(
                __('ops.dashboard.bid_intelligence.total_sessions'),
                number_format($totalSessions)
            )->description(__('ops.dashboard.bid_intelligence.total_sessions_desc')),
            Stat::make(
                __('ops.dashboard.bid_intelligence.total_lines'),
                number_format($totalLines)
            )->description(__('ops.dashboard.bid_intelligence.total_lines_desc')),
            Stat::make(
                __('ops.dashboard.bid_intelligence.avg_bidders'),
                number_format($avgBidders, 1)
            )->description(__('ops.dashboard.bid_intelligence.avg_bidders_desc')),
            Stat::make(
                __('ops.dashboard.bid_intelligence.award_updates_30d'),
                number_format($awardCoverage)
            )->description(__('ops.dashboard.bid_intelligence.award_updates_30d_desc')),
        ];
    }
}
