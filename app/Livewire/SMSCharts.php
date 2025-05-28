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

class SMSCharts extends ChartWidget
{
    protected static ?string $heading = 'SMS Summary';

    protected function getData(): array
    {
        
        $sms = DB::table('sms_analytics')
        ->select('*', DB::raw('COUNT(phone) as sms_count'))
        ->groupBy('template_id')
        ->pluck('sms_count', 'created_at')->toArray();

        $smsArray = [];
        foreach ($sms as $key => $value) {
            $carbon = Carbon::parse($key)->format('My');
            $smsArray[$carbon] = $value;
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
        
        //echo "<pre>SMS: "; print_r($sms); echo "</pre>"; die;

        return [
            'datasets' => [
                [
                    'label' => 'SMS Summary',
                    'data' => array_values($smsArray),
              'backgroundColor' => ['rgba(255, 183, 77, 0.75)', // SMS Processed – Cool Gray-Blue
],
                    'hoverOffset' => 4
                ],
            ],
            //'labels' => ['delivered']
            'labels' => array_keys($smsArray),
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
        return 'bar';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                indexAxis: 'x',
            }
        JS);
    }

}