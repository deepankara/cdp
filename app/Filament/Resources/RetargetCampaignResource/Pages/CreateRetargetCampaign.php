<?php

namespace App\Filament\Resources\RetargetCampaignResource\Pages;

use App\Filament\Resources\RetargetCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Session;
use App\Models\Segment;
use App\Models\Customers;
use App\Models\Campaign;

class CreateRetargetCampaign extends CreateRecord
{
    protected static string $resource = RetargetCampaignResource::class;

    protected function afterFill(): void
    {
        $session = Session::get('retarget_segment_array');
        if(isset($session['campaign_id']) && $session['campaign_id'] != ''){
            $segmentId = Campaign::whereId($session['campaign_id'])->value('include_segment_id');
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
            $this->data['name'] = $session['name'];
            $this->data['campaign_id'] = $session['campaign_id'];
        }
    }
}
