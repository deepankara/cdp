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
                                                    ->whereNotNull('time')
                                                    ->whereNot('status',"sent")
                                                    ->orderBy('status')
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
                        'rgba(76, 175, 80, 0.75)',   // Delivered – Elegant Green
                        'rgba(244, 67, 54, 0.75)',   // Failed – Clean Red
                        'rgba(30, 136, 229, 0.8)',   // Read – Crisp Blue
                    ],

                    'hoverOffset' => 4
                ],
            ],
            'labels' => ['delivered', 'failed', 'read']
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