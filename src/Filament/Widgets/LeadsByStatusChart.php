<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets;

use Webfloo\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Leady wg statusu';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statusOptions = Lead::getStatusOptions();
        $statusColors = [
            'new' => '#f59e0b',
            'contacted' => '#3b82f6',
            'qualified' => '#6366f1',
            'converted' => '#10b981',
            'lost' => '#ef4444',
        ];

        /** @var array<string, int> $counts */
        $counts = Lead::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn (mixed $v): int => (int) (is_numeric($v) ? $v : 0))
            ->all();

        $data = [];
        $labels = [];
        $colors = [];

        foreach ($statusOptions as $status => $label) {
            $data[] = $counts[$status] ?? 0;
            $labels[] = $label;
            $colors[] = $statusColors[$status] ?? '#9ca3af';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leady',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
