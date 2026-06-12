<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Webfloo\Models\Lead;

class LeadsBySourceChart extends ChartWidget
{
    protected ?string $heading = 'Leady wg źródła';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $sourceOptions = Lead::getSourceOptions();
        $sourceColors = [
            Lead::SOURCE_CONTACT_FORM => '#6366f1',
            Lead::SOURCE_NEWSLETTER => '#10b981',
            Lead::SOURCE_CALCULATOR => '#f59e0b',
            Lead::SOURCE_MANUAL => '#9ca3af',
            Lead::SOURCE_WEBHOOK => '#3b82f6',
            Lead::SOURCE_IMPORT => '#8b5cf6',
        ];

        /** @var array<string, int> $counts */
        $counts = Lead::query()
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->map(fn (mixed $v): int => (int) (is_numeric($v) ? $v : 0))
            ->all();

        $data = [];
        $labels = [];
        $colors = [];

        foreach ($sourceOptions as $source => $label) {
            $count = $counts[$source] ?? 0;
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $label;
                $colors[] = $sourceColors[$source] ?? '#9ca3af';
            }
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
        return 'pie';
    }
}
