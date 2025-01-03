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

class WhatsappCharts extends ChartWidget
{
    protected static ?string $heading = 'Whatsapp Summary';

    protected function getData(): array
    {
        
        $whatsapp = DB::table('whatsapp_analytics')->select('status', DB::raw('count(*) as status_count'))
        ->groupBy('status')->get()->toArray();

        $keyValueArray = [];
        
        foreach ($whatsapp as $item) {
            $keyValueArray[$item->status] = $item->status_count;
        }

        $whatsapp = $keyValueArray;

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
        
        //echo "<pre>Whatsapp: "; print_r($whatsapp); echo "</pre>"; die;

        return [
            'datasets' => [
                [
                    'label' => 'Whatsapp Summary',
                    'data' => array_values($whatsapp),
                    'backgroundColor' => [
                        'rgba(126, 84, 5, 0.53)', // Processed
                        'rgba(6, 97, 6, 0.34)', // Delivered
                        'rgba(80, 9, 15, 0.81)', // Open
                        //'rgb(175, 34, 100)', // Clicked
                    ],
                    'hoverOffset' => 4
                ],
            ],
            'labels' => ['sent', 'delivered', 'read']
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
        return 'pie';
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