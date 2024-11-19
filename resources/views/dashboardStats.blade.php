@php
        $result = DB::table('email_analytics')
            ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
            ->groupBy('event')
            ->pluck('user_count', 'event')->toArray();

        $users = DB::table('customers')->distinct()->count('email');
        $segment = DB::table('segment')->count('id');

        $campaign = DB::table('campaign')->count();

@endphp
<x-filament-panels::page>

    <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <a href="{{route('filament.admin.resources.campaigns.index')}}">
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Total Campaigns</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{$campaign}}
                    </div>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/6104/6104532.png" alt="Processed" class="w-8 h-8">
            </div>
        </div>
        </a>

        <a href="{{route('filament.admin.resources.segments.index')}}">
            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-black-500 dark:text-black-400">Total Segment</span>
                        <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                            {{$segment}}
                        </div>
                    </div>
                    <img src="https://cdn-icons-png.flaticon.com/512/4577/4577216.png" alt="Processed" class="w-8 h-8">
                </div>
            </div>
        </a>

        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Total Customers</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{$users}}
                    </div>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/3201/3201521.png" alt="Processed" class="w-8 h-8">
            </div>
        </div>
    </div>

    <a href="{{route('filament.admin.pages.email-analytics')}}">
    <h4>Email Analytics</h4>
    </a>
    <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Processed</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{$result["processed"]}}
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
                        {{$result["delivered"]}}
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
@livewire(\App\Livewire\Charts::class)
</x-filament-panels::page>