<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Customers;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Session;

class EditTemplates extends EditRecord
{
    protected static string $resource = TemplatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        if(isset($this->record['segment_id']) && $this->record['segment_id'] != ''){

            $customers = Customers::where('segment_id',$this->record['segment_id'])->first();
            if(!$customers){
                 Notification::make()
                ->title('No Customers in Segment')
                ->danger()
                ->color('danger')
                ->duration(5000)
                ->send();
            }else{
                if(isset($customers['attributes']) && $customers['attributes'] != ''){
                    $attributes = array_keys(json_decode($customers['attributes'],true));
                    array_push($attributes,"name","email","contact_no","unsubscribe");
                    $customArray = [];
                    foreach($attributes as $key => $value){
                        $customArray[$key]['value'] = '{'.$value.'}';
                        $customArray[$key]['label'] = $value;
                    }
                    Session::put('customVariables',$customArray);
                }
            }
        }
    }

    protected function beforeSave(): void
    {
        $customers = Customers::where('segment_id',$this->record['segment_id'])->first();
        if(!$customers){
            Notification::make()
            ->title('No Customers in Segment')
            ->danger()
            ->color('danger')
            ->duration(5000)
            ->send();
            $this->halt();
        }
    }
}
