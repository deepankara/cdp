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

class DashCharts extends ChartWidget
{
    protected static ?string $heading = 'Overall Analytics';

    protected function getData(): array
    {
        
        $email = DB::table('email_analytics')->distinct('sg_message_id')->count();
        $whatsapp = DB::table('whatsapp_analytics')->distinct('wa_id')->count();
        $sms = DB::table('sms_analytics')->count();

        $labels = ['Email', 'WhatsApp', 'SMS'];

        $data = [
            'Email' => $email,
            'WhatsApp' => $whatsapp,
            'SMS' => $sms,
        ];

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
                    'label' => 'Overall',
                    'data' => array_values($data),
                    'fill' => false,
                    'backgroundColor' => [
                        ' #21618c', //'rgba(116, 184, 223, 0.91)',  // Email
                        ' #239b56', // WhatsApp
                        ' #6c3483', // SMS
                    ],
                    'borderColor' => [
                        ' #21618c', //'rgba(1, 75, 117, 0.63)', // Email
                        ' #239b56', // WhatsApp
                        ' #6c3483', // SMS                        
                    ],
                    'borderWidth' => 1
                ],
            ],
            'labels' => ['Email', 'WhatsApp', 'SMS'],
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
                indexAxis: 'y',
            }
        JS);
    }

}