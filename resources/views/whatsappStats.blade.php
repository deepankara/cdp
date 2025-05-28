@php
    $whatsapp = Session::get('whatsapp_user_analytics');
@endphp
    <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Sent</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{isset($whatsapp['sent']) && $whatsapp['sent'] != '' ? $whatsapp['sent'] : 0}}
                    </div>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/17416/17416116.png" alt="Processed" class="w-8 h-8">
            </div>
        </div>

            <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-medium text-black-500 dark:text-black-400">Delivered</span>
                        <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                            {{isset($whatsapp['delivered']) && $whatsapp['delivered'] != '' ? $whatsapp['delivered'] : 0}}
                        </div>
                    </div>
                    <img src="https://cdn-icons-png.flaticon.com/512/3031/3031282.png" alt="Processed" class="w-8 h-8">
                </div>
            </div>

        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Read</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{isset($whatsapp['read']) && $whatsapp['read'] != '' ? $whatsapp['read'] : 0}}
                    </div>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/10337/10337166.png" alt="Processed" class="w-8 h-8">
            </div>
        </div>

        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-4 shadow-sm ring-1 ring-black-950/5 dark:bg-black-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-black-500 dark:text-black-400">Failed</span>
                    <div class="text-3xl font-semibold tracking-tight text-black-950 dark:text-white">
                        {{isset($whatsapp['failed']) && $whatsapp['failed'] != '' ? $whatsapp['failed'] : 0}}
                    </div>
                </div>
                <img src="https://cdn-icons-png.flaticon.com/512/1828/1828843.png" alt="Processed" class="w-8 h-8">
            </div>
        </div>
    </div>

    <br>
    <br>
@if(isset($whatsapp['custom']) && $whatsapp['custom'] == 'Show')
{{$this->table}}
@endif