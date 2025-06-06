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
    <!-- Processed -->
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm font-medium text-black-500 dark:text-black-400">Processed</span>
                <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                    {{isset($result['processed']) && $result['processed'] != '' ? $result['processed'] : 0}}

                </div>
            </div>
            <img src="https://cdn-icons-png.flaticon.com/512/1157/1157026.png" alt="Processed" class="w-8 h-8">
        </div>
    </div>

    <!-- Delivered -->
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm font-medium text-black-500 dark:text-black-400">Delivered</span>
                <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                    {{isset($result['delivered']) && $result['delivered'] != '' ? $result['delivered'] : 0}}
                </div>
            </div>
            <img src="https://cdn-icons-png.flaticon.com/512/8905/8905083.png" alt="Delivered" class="w-8 h-8">
        </div>
    </div>

    <!-- Opened -->
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm font-medium text-black-500 dark:text-black-400">Opened</span>
                <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                    {{isset($result['open']) && $result['open'] != '' ? $result['open'] : 0}}
                </div>
            </div>
            <img src="https://cdn-icons-png.flaticon.com/512/2901/2901214.png" alt="Opened" class="w-8 h-8">
        </div>
    </div>

    <!-- Clicked -->
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm font-medium text-black-500 dark:text-black-400">Clicked</span>
                <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                    {{isset($result['click']) && $result['click'] != '' ? $result['click'] : 0}}
                </div>
            </div>
            <img src="https://cdn-icons-png.flaticon.com/512/1481/1481160.png" alt="Clicked" class="w-8 h-8">
        </div>
    </div>
</div>

<br>