<x-filament-panels::page>
    @if(Session::get('email_id') != '')
        {{$this->form}}
    @else
    <x-filament-panels::form wire:submit="save"> 
        {{ $this->userForm }}
 
        <x-filament-panels::form.actions 
            :actions="$this->getFormActions()"
        /> 
    </x-filament-panels::form>
    @endif
</x-filament-panels::page>
