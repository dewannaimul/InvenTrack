<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\ChartWidget;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Sales (last 30 days)';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($daysAgo) => now()->subDays($daysAgo)->toDateString());

        $sales = SalesOrder::query()
            ->whereNotIn('status', [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])
            ->whereDate('order_date', '>=', now()->subDays(29))
            ->selectRaw('DATE(order_date) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'datasets' => [
                [
                    'label' => 'Sales revenue',
                    'data' => $days->map(fn ($day) => (float) ($sales[$day] ?? 0))->all(),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn ($day) => \Illuminate\Support\Carbon::parse($day)->format('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
