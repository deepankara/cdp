<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmailCharts extends ChartWidget
{
    protected static ?string $heading = 'Email Analytics Chart';

    protected function getData(): array
    {
        $date = Carbon::today(); // Set the date for filtering

        // Query the EmailAnalyticsTable to get counts grouped by event
        $data = DB::table('email_analytics')
            ->select('event', DB::raw('COUNT(*) as count'))
            ->whereDate('created_at', $date)
            ->groupBy('event')
            ->pluck('count', 'event'); // Returns an associative array like ['processed' => 10, 'opened' => 5]

        // Define labels and ensure all events are represented
        $events = ['processed', 'delivered', 'open', 'click'];
        $counts = collect($events)->map(fn($event) => $data[$event] ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'Emails',
                    'data' => $counts->toArray(),
                    'backgroundColor' => ['#FFCC00', '#4CAF50', '#2196F3', '#FFC107'], // Add specific colors for events
                ],
            ],
            'labels' => $events,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
