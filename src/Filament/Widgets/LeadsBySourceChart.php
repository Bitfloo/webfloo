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
            'contact_form' => '#6366f1',
            'newsletter' => '#10b981',
            'calculator' => '#f59e0b',
            'manual' => '#9ca3af',
            'webhook' => '#3b82f6',
            'import' => '#8b5cf6',
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
