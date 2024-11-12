<x-filament-panels::page>
    @if(Session::get('camp_id') != '')
        {{$this->form}}
    @else
    <x-filament-panels::form wire:submit="save"> 
        {{ $this->campaignForm }}
 
        <x-filament-panels::form.actions 
            :actions="$this->getFormActions()"
        /> 
    </x-filament-panels::form>
    @endif
   
</x-filament-panels::page>
