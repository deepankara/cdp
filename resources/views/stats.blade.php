@php
if(isset(Session::get('email_analytics')['campaign_id']) && Session::get('email_analytics')['campaign_id'] != ''){
        $result = DB::table('email_analytics')
            ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
            ->where('campaign_id', Session::get('email_analytics')['campaign_id'])
            ->groupBy('event')
            ->pluck('user_count', 'event')->toArray();
}else{
    return '';
}
@endphp
<div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2 xl:grid-cols-4">
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10" style="background: lightblue;">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-2">
                <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-black-500 dark:text-black-400">
                    Processed
                </span>
            </div>
            <div class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                <span style="color:#4A90E2">{{$result["processed"]}}</span>
            </div>
        </div>
    </div>
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10" style="background: blue;">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-2">
                <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-black-500 dark:text-black-400">
                    Delivered
                </span>
            </div>
            <div class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                <span style="color:#4A90E2">{{$result["delivered"]}}</span>
            </div>
        </div>
    </div>
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10" style="background: yellow;">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-2">
                <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-black-500 dark:text-black-400">
                    Opened
                </span>
            </div>
            <div class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                <span style="color:#4A90E2">{{isset($result['open']) && $result['open'] != '' ? $result['open'] : 0}}</span>
            </div>
        </div>
    </div>
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10" style="background: green;">
        <div class="grid gap-y-2">
            <div class="flex items-center gap-x-2">
                <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-black-500 dark:text-black-400">
                    Clicked
                </span>
            </div>
            <div class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                <span style="color:#4A90E2">{{isset($result['click']) && $result['click'] != '' ? $result['click'] : 0}}</span>
            </div>
        </div>
    </div>
</div>
</br>
@livewire(\App\Filament\Widgets\EmailAnalyticsBar::class)