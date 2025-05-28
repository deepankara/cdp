<x-filament-panels::page.simple>
    <script>
        if ({{ Auth::check() ? 'true' : 'false' }}) {
            window.location.href = "/admin";
        }
    </script>
    
    <x-filament::button href="{{ \Filament\Facades\Filament::getUrl() }}" tag="a">
        @lang('filament-email-2fa::filament-email-2fa.continue')
    </x-filament::button>
</x-filament-panels::page.simple>
