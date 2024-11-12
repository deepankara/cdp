<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

class EmailStatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?string $pollingInterval = null;
    protected function getStats(): array
    {
        if(isset(Session::get('email_analytics')['campaign_id']) && Session::get('email_analytics')['campaign_id'] != ''){

            $result = DB::table('email_analytics')
                        ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
                        ->where('campaign_id', Session::get('email_analytics')['campaign_id'])
                        ->groupBy('event')
                        ->pluck('user_count', 'event')->toArray();
            return [
                Stat::make('Processed', $result['processed'])->value(new HtmlString('<span style="color:#4A90E2">'.$result["processed"].'</span>')),
                Stat::make('Delivered', $result['delivered'])->value(new HtmlString('<span style="color:#3498DB">'.$result["delivered"].'</span>')),
                Stat::make('Opened', $result['open'])->value(new HtmlString('<span style="color:#F1C40F">'.$result["open"].'</span>')),
                Stat::make('Clicked', isset($result['click']) && $result['click'] != '' ? $result['click'] : 0)->value(new HtmlString('<span style="color:#28A745">'.$result["click"].'</span>')),
            ];
        }else{
            return [

            ];
        }
    }
}
