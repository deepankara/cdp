<?php

namespace App\Filament\Resources\CampaignResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EmailAnalyticsWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?string $pollingInterval = null;
    protected function getStats(): array
    {
        $result = DB::table('email_analytics')
                    ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
                    ->where('campaign_id', Session::get('camp_id'))
                    ->groupBy('event')
                    ->pluck('user_count', 'event')->toArray();
        return [
            Stat::make('Processed', $result['processed']),
            Stat::make('Delivered', $result['delivered']),
            Stat::make('Opened', isset($result['open']) && $result['open'] != '' ? $result['open'] : 0),
            Stat::make('Clicked', isset($result['click']) && $result['click'] != '' ? $result['click'] : 0),
        ];
    }
}
