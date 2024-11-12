<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class EmailAnalyticsBar extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static bool $isLazy = false;
    protected static ?string $pollingInterval = null;

    // protected function getFilters(): ?array
    // {
    //     return [
    //         'today' => 'Today',
    //         'week' => 'Last week',
    //         'month' => 'Last month',
    //         'year' => 'This year',
    //     ];
    // }

    protected function getData(): array
    {



        

        if(isset(Session::get('email_analytics')['campaign_id']) && Session::get('email_analytics')['campaign_id'] != ''){

            $result = DB::table('email_analytics')
                        ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
                        ->where('campaign_id', Session::get('email_analytics')['campaign_id'])
                        ->groupBy('event')
                        ->pluck('user_count', 'event')->toArray();
                        return [
                            'datasets' => [
                                [
                                    'label' => 'Email Analytics',
                                    'data' => [$result['processed'], $result['delivered'], $result['open'], $result['click']],
                                    'backgroundColor' => ['#4A90E2',"#3498DB","#F1C40F","#28A745"],
                                    'borderColor' => ['#4A90E2',"#3498DB","#F1C40F","#28A745"],
                                ],
                            ],
                            'labels' => ['Processed', 'Delivered', 'Opened', 'Clicked'],
                        ];
        }else{
            return [

            ];
        }
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
