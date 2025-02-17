<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SendgridController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use App\Models\SmsAnalytics;
use Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;

class ApiController extends BaseController
{
    public function sendMessage(Request $request){
        $requestData = $request->all();
        $json = [];
        if($requestData['channel'] == 'email'){
            $template = DB::table('templates')->whereId($requestData['template_id'])->get()->toArray();
            $template = (array) current($template);
            $html_content = $template['html_content'];
            $attributes = $requestData['template_attr'];
            $attributes = array_merge($requestData,$attributes);
            $unsubscribeUrl = 'https://example.com/unsubscribe?email=' . urlencode($attributes['email']);
            $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            $attributes['html_content'] = $customerHtmlContent;
            // $isSendMessage = EmailController::sendTelSpielEmail($attributes,$attributes);
            $isSendMessage = SendgridController::sendGridEmail($attributes,$attributes);
        }

        if($requestData['channel'] == 'sms'){
            $template = DB::table('sms_templates')->whereId($requestData['template_id'])->get()->toArray();
            $template = (array) current($template);

            $dltTemplateId = $template['dlt_template_id'];

            $html_content = $template['sms'];
            $attributes = $requestData['template_attr'];
            
            $attributes = array_merge($requestData,$attributes);
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            
            $isSendMessage = SmsController::sendSms($customerHtmlContent,$requestData['mobile_no'],$requestData['template_id'],$dltTemplateId);
        }

        if ($requestData['channel'] == 'whatsapp') {
            $component = [];
            $template = DB::table('whatsapp_templates')->where("name",$requestData['template_id'])->get()->toArray();
            $template = (array) current($template);
            if (isset($template['header_type']) && $template['header_type'] != 'NONE') {
                $header = [];
                $header['type'] = "HEADER";
                $header['parameters'] = [];

                if($template['header_type'] == "VIDEO"){
                    $header['parameters'][0] = [
                        "type" => "video",
                        "video" => ['link'=>$requestData['template_attr']['header_value']['value']]
                    ];
                }

                if($template['header_type'] == "TEXT"){
                    $header['parameters'][0] = [
                        "type" => "text",
                        "text" => $requestData['template_attr']['header_value']['value']
                    ];
                }
                $component[] = $header;
            }

            if (isset($requestData['template_attr']['body_value']) && !empty($requestData['template_attr']['body_value'])) {
                $body = [];
                $body['type'] = "BODY";
                $body['parameters'] = [];
        
                // Loop through the body_value array and create parameter elements
                foreach ($requestData['template_attr']['body_value'] as $index => $text) {
                    $body['parameters'][] = [
                        "type" => "text",
                        "text" => $text
                    ];
                }
                $component[] = $body;
            }

            if(isset($requestData['template_attr']['button']) && $requestData['template_attr']['button'] != ''){
                $buttonJson = json_decode($template['buttons'],true);
                foreach($buttonJson as $key => $value){
                    if($value['option'] == "COPY_CODE"){
                        $button = [];
                        $button['type'] = "button";
                        $button['sub_type'] = strtolower($value['option']);
                        $button['index'] = $key;
                        // $button['parameters'][] = [];
                        $button['parameters']['type'] = "coupon_code";
                        $button['parameters']['coupon_code'] = $requestData['template_attr']['button']['button_offer_code'];
                        $button['parameters'] = array($button['parameters']);
                        $component[] = $button;
                    }

                    if($value['option'] == "URL"){
                        $button = [];
                        $button['type'] = "button";
                        $button['sub_type'] = strtolower($value['option']);
                        $button['index'] = $key;
                        // $button['parameters'][] = [];
                        $button['parameters']['type'] = "text";
                        $button['parameters']['text'] = $requestData['template_attr']['button']['button_dynamic_url'];
                        $button['parameters'] = array($button['parameters']);
                        $component[] = $button;
                    }
                }
            }

            
            // echo "<pre>";print_r($component);exit;
            
            $whatsapp = [];
            $whatsapp['messaging_product'] = "whatsapp";
            $whatsapp['to'] = $requestData['mobile_no'];
            $whatsapp['type'] = "template";
            $whatsapp['template'] = [];
            $whatsapp['template']['name'] = $requestData['template_id'];
            $whatsapp['template']['language'] = [
                "code" => "en_us"
            ];
            $whatsapp['template']['components'] = $component;

            // echo "<pre>";print_r(json_encode($whatsapp));exit;

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/105029218931496/messages',
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
            if(isset($response['messages'][0]['id']) && $response['messages'][0]['id'] != ''){
                $json['wa_id'] = $response['messages'][0]['id'];
                $json['template'] = $requestData['template_id'];
                
                $statuses = ["read", "sent", "delivered", "failed"];
                $whatsappData = [];
            
                foreach ($statuses as $status) {
                    $whatsappData[] = [
                        'wa_id' => $response['messages'][0]['id'],
                        'status' => $status,
                        'mobile_number' => str_replace('+','',$requestData['mobile_no']),
                        'created_at' => Carbon::now(),
                        'template_id' => $requestData['template_id'],
                    ];
                }
                DB::table("whatsapp_analytics")->insert($whatsappData);
            }
        }
        $isSendMessage = 200;
        $json['email'] = isset($requestData['email']) && $requestData['email'] != '' ? $requestData['email'] : null;
        $json['mobile_no'] = isset($requestData['mobile_no']) && $requestData['mobile_no'] != '' ? $requestData['mobile_no'] : null;
        $json['client_id'] = $request->user()->id;
        $json['channel'] = $requestData['channel'];
        $json['endpoint'] = $request->url();
        $json['status'] = $isSendMessage;
        $json['payload'] = json_encode($requestData);
        $json['created_at'] = Carbon::now();
        DB::table('api_logs')->insert($json);
        return $isSendMessage;
    }
}