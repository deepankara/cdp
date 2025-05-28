<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;
use App\Models\Segment;
use App\Models\Customers;
use Filament\Notifications\Notification;


class CreateTemplates extends CreateRecord
{
    protected static string $resource = TemplatesResource::class;

    protected function afterFill(): void
    {
        $session = Session::get('segment_array');
        if(isset($session['segment_id']) && $session['segment_id'] != ''){
            $customers = Customers::where('segment_id',$session['segment_id'])->first();
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
            $this->data['name'] = $session['name'];
            $this->data['segment_id'] = $session['segment_id'];
        }
    }

    protected function beforeCreate(): void
    {
        $customers = Customers::where('segment_id',$this->data['segment_id'])->first();
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
