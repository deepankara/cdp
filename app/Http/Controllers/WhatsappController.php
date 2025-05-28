<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use App\Models\SmsAnalytics;
use Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;
use App\Jobs\WhatsappWebhook;


class WhatsappController extends BaseController
{
    public function whatsappWebook(Request $request){
        $requestData = $request->all();
        // echo $requestData['hub_challenge'];exit;
        Log::info(json_encode($requestData));    
        WhatsappWebhook::dispatch($requestData);
        return response()->json(['message' => 'Successfully!']);
        // $statusData = $requestData['entry'][0]['changes'][0]['value']['statuses'][0];
        // $whatsapp = [];
        // if($statusData['status'] == 'failed'){
        //     DB::table('whatsapp_analytics')->whereNot('status','failed')
        //                                     ->where('wa_id',$statusData['id'])
        //                                     ->delete();
            
           
        // }

        // if($statusData['status'] == 'delivered'){
        //     DB::table('whatsapp_analytics')->where('status','failed')
        //                                     ->where('wa_id',$statusData['id'])
        //                                     ->delete();

        // }

        // DB::table('whatsapp_analytics')->where('status',$statusData['status'])
        //                                 ->where('wa_id',$statusData['id'])
        //                                 ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);
        

        // $whatsapp['status']  = $statusData['status'];
        // $whatsapp['mobile_number']  = $statusData['recipient_id'];
        // $whatsapp['wa_id']  = $statusData['id'];
        // $whatsapp['time']  = $this->convertToIST($statusData['timestamp']);
        // $whatsapp['created_at']  = Carbon::now();
        // DB::table('whatsapp_analytics')->insert($whatsapp);
    }

    public function sendWa(Request $request){
        $campaign = DB::table('campaign')->where('channel',"Whatsapp")->where('campaign_executed',false)->get()->toArray();
        $campaign = (array) current($campaign);

        $customers = DB::table('customers')
        ->where('segment_id', $campaign['include_segment_id'])
     ;
    

        $whatsappVariables = json_decode($campaign['wa_variables'],true);

        if(isset($campaign['rule_id']) && $campaign['rule_id'] != '') {
            $customers = RulesController::rulesSync($campaign['rule_id'],$customers);
        }
        
        
        $template = DB::table('whatsapp_templates')->where("id",$campaign['whatsapp_template'])->get()->toArray();
        $template = (array) current($template);


        if($template['category'] == "MARKETING"){
            $customers = $customers->leftJoin('whatsapp_unsubscribe', function ($join) {
                            $join->on(DB::raw('RIGHT(customers.contact_no, 10)'), '=', DB::raw('RIGHT(whatsapp_unsubscribe.number, 10)'));
                        })
                        ->whereNull('whatsapp_unsubscribe.number');
        }
        $customers = $customers->get()->toArray();

        echo "<pre>";print_r($customers);exit;



        
        foreach ($customers as $customerKey => $customerValue) {
            $component = [];
            $convertArray = (array) $customerValue;
            $attributes = json_decode($customerValue->attributes,true);
            $attributes = array_merge($convertArray,$attributes);


            if (isset($template['header_type']) && $template['header_type'] != 'NONE') {
                $header = [];
                $header['type'] = "HEADER";
                $header['parameters'] = [];

                if($template['header_type'] == "VIDEO"){
                    $header['parameters'][0] = [
                        "type" => "video",
                        "video" => ['link'=>$attributes[$whatsappVariables[0]['value']]]
                    ];
                }

                if($template['header_type'] == "IMAGE"){
                    $header['parameters'][0] = [
                        "type" => "image",
                        "image" => ['link'=>$attributes[$whatsappVariables[0]['value']]]
                    ];
                }

                if($template['header_type'] == "DOCUMENT"){
                    $header['parameters'][0] = [
                        "type" => "document",
                        "document" => ['link'=>$attributes[$whatsappVariables[0]['value']]]
                    ];
                }

                if($template['header_type'] == "TEXT"){
                    $header['parameters'][0] = [
                        "type" => "text",
                        "text" => $attributes[$whatsappVariables[0]['value']]
                    ];
                }
                $component[] = $header;
            }


            if (isset($template['html_content']) && str_contains($template['html_content'],"{{")) {
                $body = [];
                $body['type'] = "BODY";
                $body['parameters'] = [];
                foreach ($whatsappVariables as $index => $text) {
                    if($text['type'] == "body"){
                        $body['parameters'][] = [
                            "type" => "text",
                            "text" => $attributes[$text['value']]
                        ];
                    }
                }
                $component[] = $body;
            }


            if(isset($template['buttons']) && $template['buttons'] != ''){
                $buttonJson = json_decode($template['buttons'],true);
                foreach($buttonJson as $key => $value){
                    if($value['option'] == "COPY_CODE"){
                        $button = [];
                        $button['type'] = "button";
                        $button['sub_type'] = strtolower($value['option']);
                        $button['index'] = $key;
                        // $button['parameters'][] = [];
                        $button['parameters']['type'] = "coupon_code";
                        $option = $value['option'];
                        $result = Arr::first($whatsappVariables, function ($item) use ($option) {
                            return $item['name'] === $option;
                        });
                        $button['parameters']['coupon_code'] = $attributes[$result['value']];
                        $button['parameters'] = array($button['parameters']);
                        $component[] = $button;
                    }

                    if($value['option'] == "URL" && $value['url_type'] == 'dynamic'){
                        if(isset($value['dynamic_url']) && $value['dynamic_url'] != ''){
                            if(!str_contains(($value['dynamic_url']),"{{1}}")){
                                continue;
                            }
                        }else{
                            continue;
                        }
                        $button = [];
                        $button['type'] = "button";
                        $button['sub_type'] = strtolower($value['option']);
                        $button['index'] = $key;
                        // $button['parameters'][] = [];
                        $button['parameters']['type'] = "text";
                        $option = $value['option'];

                        $result = Arr::first($whatsappVariables, function ($item) use ($option) {
                            return $item['name'] === $option;
                        });
                        $button['parameters']['text'] =  $attributes[$result['value']];
                        $button['parameters'] = array($button['parameters']);
                        $component[] = $button;
                    }
                }
            }
            echo "<pre>";print_r($component);
            $whatsapp = [];
            $whatsapp['messaging_product'] = "whatsapp";
            $whatsapp['to'] = $attributes['contact_no'];
            $whatsapp['type'] = "template";
            $whatsapp['template'] = [];
            $whatsapp['template']['name'] = $template['name'];
            $whatsapp['template']['language'] = [
                "code" => "en_US"
            ];
            $whatsapp['template']['components'] = $component;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                ),
            ));


            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            echo "<pre>";print_r($response);exit;


            if (isset($response['messages'][0]['id']) && $response['messages'][0]['id'] != '') {
                $statuses = ["read", "sent", "delivered", "failed"];
                $whatsappData = [];
                foreach ($statuses as $status) {
                    $whatsappData[] = [
                        'wa_id' => $response['messages'][0]['id'],
                        'status' => $status,
                        'mobile_number' => str_replace('+','',$attributes['contact_no']),
                        'created_at' => Carbon::now(),
                        'campaign_id' => $campaign['id'],
                    ];
                }
                DB::table("whatsapp_analytics")->insert($whatsappData);
            }
        }
        exit;
    }
}