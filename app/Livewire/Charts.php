<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use DB;

class Charts extends ChartWidget
{
    protected static ?string $heading = 'Last 4 Weeks Email Analytics';

    protected function getData(): array
    {
        // Calculate the start date (4 weeks ago) and end date (today)
        $startDate = Carbon::now()->subWeeks(4)->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();

        // Query the email_analytics table to get counts grouped by event and week
        $data = DB::table('email_analytics')
            ->select(
                DB::raw('YEARWEEK(indian_time) as week'), // Group by week
                'event',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('indian_time', [$startDate, $endDate])
            ->groupBy('week', 'event')
            ->get();



        // Define the events to ensure consistency
        $events = ['processed', 'delivered', 'open', 'click'];

        // Initialize counts for the last 4 weeks
        $weeks = collect(range(0, 3))->map(fn($i) => Carbon::now()->subWeeks(3 - $i)->format('YW'))->toArray();

        // Prepare datasets for the chart
        $weeklyData = collect($weeks)->mapWithKeys(fn($week) => [
            $week => collect($events)->mapWithKeys(fn($event) => [$event => 0])->toArray(),
        ]);
        
        
        // Populate the data with actual values from the query
        foreach ($data as $row) {
            $week = $row->week; // Example: '202347'
            $event = $row->event;
            $count = $row->count;

            
            if (isset($weeklyData[$week])) {
        $weeklyData[$week] = collect($weeklyData[$week])->put($event, $count)->toArray();
    }
        }



        // Extract labels and datasets for the chart
        $labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];// Weeks as labels

        $datasets = collect($events)->map(fn($event) => [
            'label' => ucfirst($event), // Capitalize event names
            'data' => $weeklyData->map(fn($week, $key) => [
        'week' => substr($key, 0, 4) . '-' . substr($key, 4), // Format week as "2024-44"
        'value' => $week[$event],
    ])->pluck('value')->toArray(),
            'backgroundColor' => match ($event) {
                'processed' => '#FFCC00',
                'delivered' => '#4CAF50',
                'open' => '#2196F3',
                'click' => '#FFC107',
                default => '#CCCCCC',
            },
        ])->values()->toArray();

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
