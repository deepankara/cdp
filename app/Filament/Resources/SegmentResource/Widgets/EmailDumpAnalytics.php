<?php

namespace App\Filament\Resources\SegmentResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EmailDumpAnalytics extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?string $pollingInterval = null;
    protected function getStats(): array
    {
        $result = DB::table('email_dump')
                    ->select('status', DB::raw('COUNT(DISTINCT email) as user_count'))
                    ->where('segment_id',Session::get('segment_id'))
                    ->groupBy('status')
                    ->pluck('user_count', 'status')->toArray();
        return [
            Stat::make('Processed', array_sum($result)),
            Stat::make('Valid', isset($result['clean']) ? $result['clean'] : 0),
            Stat::make('Invalid', isset($result['dirty']) ? $result['dirty'] : 0),
        ];
    }
}
