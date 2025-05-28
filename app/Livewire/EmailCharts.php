<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use DB;
use Filament\Support\RawJs;
use App\Models\EmailAnalyticsApiTable;
use App\Models\WhatsappAnalytics;
use App\Models\SmsAnalytics;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Collection;

class EmailCharts extends ChartWidget
{
    protected static ?string $heading = 'Email Summary';

    protected function getData(): array
    {
        
        $email = DB::table('email_analytics')
        ->select('event', DB::raw('COUNT(DISTINCT sg_message_id) as user_count'))
        ->groupBy('event')
        ->orderBy('event')
        ->pluck('user_count', 'event')->toArray();

        if(isset($email['invalid']) && $email['invalid'] != ''){
            unset($email['invalid']);
        }

        // $datasets = collect($data)->map(fn($value, $label) => [
        //     'label' => $label,
        //     'data' => ['source'=>$label,'value'=>$value],
        //     'backgroundColor' => match ($label) {
        //         'Email' => '#FFCC00',
        //         'WhatsApp' => '#4CAF50',
        //         'SMS' => '#2196F3',
        //         default => '#CCCCCC',
        //     },
        //     ])->values()->toArray();
        

        return [
            'datasets' => [
                [
                    'label' => 'Email Summary',
                    'data' => array_values($email),
                    // 'backgroundColor' => [
                    //     'rgb(175, 34, 100)', // Clicked
                    //     'rgba(46, 81, 196, 0.72)', // Delivered
                    //     'rgba(180, 165, 28, 0.66)', // Open
                    //     'rgba(199, 75, 137, 0.92)', // Processed
                    // ],
                 'backgroundColor' => [
                     'rgba(103, 58, 183, 0.8)',    // Clicked – Professional Blue (action)
                     'rgba(76, 175, 80, 0.8)',     // Delivered – Soft Green (success)
                     'rgba(255, 193, 7, 0.8)',     // Open – Amber (attention)
                    'rgba(158, 158, 158, 0.8)',   // Processed – Neutral Gray (background)
                ],
                    'hoverOffset' => 4
                ],
            ],
            'labels' => ['click', 'delivered', 'open',"processed"]
        ];
      

        //     $startDate = now()->subMonths(3)->startOfMonth();
        //     $endDate = now()->endOfMonth();

        //     $emailData = Trend::model(EmailAnalyticsApiTable::class)
        //     ->between(
        //         start: $startDate,
        //         end: $endDate,
        //     )
        //     ->perMonth()
        //     ->count('sg_message_id');

        // $whatsappData = Trend::model(WhatsappAnalytics::class)
        //     ->between(
        //         start: $startDate,
        //         end: $endDate,
        //     )
        //     ->perMonth()
        //     ->count('wa_id');

        // $smsData = Trend::model(SmsAnalytics::class)
        //     ->between(
        //         start: $startDate,
        //         end: $endDate,
        //     )
        //     ->perMonth()
        //     ->count();       

        // return [
        //     'datasets' => [
        //         [
        //             'label' => 'Emails',
        //             'data' => $emailData->map(fn (TrendValue $value) => $value->aggregate),
        //         ],
        //         [
        //             'label' => 'WhatsApp',
        //             'data' => $whatsappData->map(fn (TrendValue $value) => $value->aggregate),
        //         ],
        //         [
        //             'label' => 'SMS',
        //             'data' => $smsData->map(fn (TrendValue $value) => $value->aggregate),
        //         ],
        //     ],
        //     'labels' => $emailData->map(fn (TrendValue $value) => $value->date),
        // // ];

    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                x: {
                    display : false
                },
                y: {
                    display : false
                }
            },
        }
        JS);
    }

}