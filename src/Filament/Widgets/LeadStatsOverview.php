<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Webfloo\Models\Lead;

class LeadStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $newLeads = Lead::new()->count();
        $convertedLeads = Lead::converted()->count();
        $conversionRate = $totalLeads > 0
            ? round(($convertedLeads / $totalLeads) * 100, 1)
            : 0;

        // Trend calculation (last 30 days vs previous 30 days)
        $currentPeriodLeads = Lead::where('created_at', '>=', now()->subDays(30))->count();
        $previousPeriodLeads = Lead::whereBetween('created_at', [
            now()->subDays(60),
            now()->subDays(30),
        ])->count();

        $trend = $previousPeriodLeads > 0
            ? round((($currentPeriodLeads - $previousPeriodLeads) / $previousPeriodLeads) * 100, 1)
            : 0;

        // Pipeline value
        $pipelineValue = Lead::inPipeline()->sum('estimated_value');

        return [
            Stat::make('Wszystkie Leady', (string) $totalLeads)
                ->description($trend >= 0 ? "+{$trend}% ten miesiąc" : "{$trend}% ten miesiąc")
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger')
                ->chart($this->getLeadsChartData()),

            Stat::make('Nowe Leady', (string) $newLeads)
                ->description('Oczekujące na kontakt')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Konwersja', "{$conversionRate}%")
                ->description("{$convertedLeads} skonwertowanych")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Wartość Pipeline', number_format((float) $pipelineValue, 0, ',', ' ').' PLN')
                ->description(Lead::inPipeline()->count().' leadów w pipeline')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }

    /**
     * Get leads chart data for the last 7 days (single query instead of N+1).
     *
     * @return array<int>
     */
    private function getLeadsChartData(): array
    {
        // Single grouped query instead of 7 separate queries
        $counts = Lead::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date')
            ->all();

        // Build array for last 7 days (oldest to newest)
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $counts[$date] ?? 0;
            $data[] = is_numeric($count) ? (int) $count : 0;
        }

        return $data;
    }
}
