<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use DB;
use Filament\Support\RawJs;

class ChartsStats extends ChartWidget
{
    protected static ?string $heading = 'Dashboard';

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
                'backgroundColor' => [
                    '#FFCC00', // Email
                    '#4CAF50', // WhatsApp
                    '#2196F3', // SMS
                ],
            ],
        ],
        'labels' => ['Email', 'WhatsApp', 'SMS'],
    ];

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