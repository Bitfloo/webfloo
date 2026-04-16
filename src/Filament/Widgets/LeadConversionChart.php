<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webfloo\Models\Lead;

class LeadConversionChart extends ChartWidget
{
    protected ?string $heading = 'Konwersja w czasie';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $start = Carbon::now()->subMonths(5)->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Single GROUP BY za wszystkie 6 miesięcy "nowych leadów" — zamiast 6 COUNT-ów w pętli.
        // Używamy DB::table() zamiast Lead::query() bo phpstan nie wie o custom aliasach
        // selectRaw (y, m, total) i generuje property.notFound na Lead.
        $newByMonth = [];
        foreach (DB::table('leads')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get() as $row) {
            /** @var object{y: int|string, m: int|string, total: int|string} $row */
            $key = sprintf('%04d-%02d', (int) $row->y, (int) $row->m);
            $newByMonth[$key] = (int) $row->total;
        }

        // Drugi GROUP BY za skonwertowane — po converted_at, tylko status=converted.
        $convertedByMonth = [];
        foreach (DB::table('leads')
            ->where('status', Lead::STATUS_CONVERTED)
            ->whereBetween('converted_at', [$start, $end])
            ->selectRaw('YEAR(converted_at) as y, MONTH(converted_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get() as $row) {
            /** @var object{y: int|string, m: int|string, total: int|string} $row */
            $key = sprintf('%04d-%02d', (int) $row->y, (int) $row->m);
            $convertedByMonth[$key] = (int) $row->total;
        }

        $months = [];
        $newLeads = [];
        $convertedLeads = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = sprintf('%04d-%02d', $date->year, $date->month);
            $months[] = $date->translatedFormat('M Y');
            $newLeads[] = $newByMonth[$key] ?? 0;
            $convertedLeads[] = $convertedByMonth[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nowe leady',
                    'data' => $newLeads,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Skonwertowane',
                    'data' => $convertedLeads,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
