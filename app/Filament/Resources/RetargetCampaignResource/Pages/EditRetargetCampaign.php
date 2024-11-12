<?php

namespace App\Filament\Resources\RetargetCampaignResource\Pages;

use App\Filament\Resources\RetargetCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Session;
use App\Models\Segment;
use App\Models\Customers;
use App\Models\Campaign;

class EditRetargetCampaign extends EditRecord
{
    protected static string $resource = RetargetCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        if(isset($this->record['campaign_id']) && $this->record['campaign_id'] != ''){
            $segmentId = Campaign::whereId($this->record['campaign_id'])->value('include_segment_id');
            $customers = Customers::where('segment_id',$segmentId)->first()->toArray();
            if(isset($customers['attributes']) && $customers['attributes'] != ''){
                $attributes = array_keys(json_decode($customers['attributes'],true));
                array_push($attributes,"name","email","contact_no",'unsubscribe');
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
