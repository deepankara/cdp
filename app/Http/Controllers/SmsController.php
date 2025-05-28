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
use App\Jobs\SmsWebhook;


class SmsController extends BaseController
{
    public static function sendSms($sms,$phoneNumber,$templateId,$dltTemplateId){
        if($dltTemplateId == "1607100000000073661"){
            $userName = "AuxiloOTP";
            $apiKey = env('TELSPICE_SMS_OTP_KEY');
        }else{
            $userName = "AuxiloTr";
            $apiKey = env('TELSPICE_SMS_KEY');
        }
        $curl = curl_init();
        $json = [
            "username" => $userName,
            "dest" => str_replace('91','',$phoneNumber),
            "apikey" => $apiKey,
            "signature" => "AUXILO",
            "msgtype" => "PM",
            "msgtxt" => $sms,
            "entityid" => env('TELSPICE_SMS_ENTITY_ID'),
            'templateid' => $dltTemplateId,
            'custref' => "TEST"
        ];


        $url = "https://api.telsp.in/pushapi/sendbulkmsg?" . http_build_query($json, '', '&', PHP_QUERY_RFC3986);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
          
        $response = curl_exec($curl);
        $response = json_decode($response,true);
        // echo "<pre>";print_r($sms);exit;
        $response = (array) current($response);
        if(isset($response['code']) && $response['code'] == 6001){
            $smsAnalytics = [];
            $smsAnalytics['template_id'] = $templateId;
            $smsAnalytics['phone'] = $phoneNumber;
            $smsAnalytics['sms'] = $sms;
            $smsAnalytics['status'] = "Processed";
            $smsAnalytics['request_id'] = $response['reqId'];
            $smsAnalytics['created_at'] = Carbon::now();
            return DB::table('sms_analytics')->insert($smsAnalytics);
        }else{
            return false;
        } 

          
        // $json = [
        //     "phone" => str_replace('91','',$phoneNumber),
        //     "message"=>$sms
        // ];
        // $json = json_encode($json);

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://wsnpp7trhj.execute-api.ap-south-1.amazonaws.com/dev/api/sendmessage',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS =>$json,
        //     CURLOPT_HTTPHEADER => array(
        //       'Content-Type: application/json'
        //     ),
        //   ));

        // $curl = curl_init();
        // $postFields = [
        //     'userid' => env('SMS_USER_ID'),
        //     'password' => env('SMS_PASSWORD'),
        //     'send_to' => $phoneNumber,
        //     'method' => 'SendMessage',
        //     'msg' => $sms,
        //     'msg_type' => 'TEXT',
        //     'auth_scheme' => 'plain',
        //     'v' => '1.1',
        //     'format' => 'json'
        // ];

        // curl_setopt_array($curl, [
        //     CURLOPT_URL => 'https://enterprise.smsgupshup.com/GatewayAPI/rest',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS => http_build_query($postFields), // Convert array to URL-encoded query string
        //     CURLOPT_HTTPHEADER => [
        //         'Content-Type: application/x-www-form-urlencoded'
        //     ],
        // ]);
    }

    public function sendCampSms(Request $request){
        $campaign = DB::table('campaign')->where('channel',"SMS")->get()->toArray();
        $campaign = (array) current($campaign);

        $customers = DB::table('customers')->where('segment_id',$campaign['include_segment_id']);

        $smsVariables = json_decode($campaign['sms_variables'],true);

        if(isset($campaign['rule_id']) && $campaign['rule_id'] != '') {
            $customers = $this->rulesSync($campaign['rule_id'],$customers);
        }else{
            $customers = $customers->get()->toArray();
        }

        $smsTemplates = DB::table('sms_templates')->where("id",$campaign['sms_template'])->get()->toArray();
        $smsTemplates = (array) current($smsTemplates);
        $mainTemplate = $smsTemplates['sms'];
        $dltTemplateId = $smsTemplates['dlt_template_id'];

        $smsVar = [];
        foreach($smsVariables as $key => $value){
            $smsVar[$value['name']] = $value['value'];
        }

        foreach ($customers as $customerKey => $customerValue) {
            $convertArray = (array) $customerValue;
            $attributes = json_decode($customerValue->attributes,true);
            $attributes = array_merge($convertArray,$attributes);

            $template = $mainTemplate;
            foreach ($smsVar as $placeholder => $key) {
                $value = $whatsapp[$key] ?? $attributes[$key] ?? '';
                $template = str_replace("{{{$placeholder}}}", $value, $template);
            }
            $phoneNumber = $attributes['contact_no'];
            $json = [
                "username" => "AuxiloTr",
                "dest" => str_replace('91','',$phoneNumber),
                "apikey" => env('TELSPICE_SMS_KEY'),
                "signature" => "AUXILO",
                "msgtype" => "PM",
                "msgtxt" => $template,
                "entityid" => env('TELSPICE_SMS_ENTITY_ID'),
                'templateid' => $dltTemplateId,
                'custref' => "Test"
            ];

            $url = "https://api.telsp.in/pushapi/sendbulkmsg?" . http_build_query($json, '', '&', PHP_QUERY_RFC3986);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            // $json = [
            //     "phone" => str_replace('91','',$phoneNumber),
            //     "message"=>$template
            // ];

            // $json = json_encode($json);

            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => 'https://wsnpp7trhj.execute-api.ap-south-1.amazonaws.com/dev/api/sendmessage',
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'POST',
            //     CURLOPT_POSTFIELDS =>$json,
            //     CURLOPT_HTTPHEADER => array(
            //     'Content-Type: application/json'
            //     ),
            // ));
            
            $response = curl_exec($curl);
            $responses = json_decode($response,true);
            foreach($responses as $response){
                if(isset($response['code']) && $response['code'] != ''){
                    $smsAnalytics = [];
                    $smsAnalytics["campaign_id"] = $campaign['id'];
                    $smsAnalytics['phone'] = $phoneNumber;
                    $smsAnalytics['sms'] = $template;
                    $smsAnalytics['status'] = "Processed";
                    $smsAnalytics['request_id'] = $response['reqId'];
                    $smsAnalytics['created_at'] = Carbon::now();
                    DB::table('sms_analytics')->insert($smsAnalytics);
                }
            }
            echo "<pre>";print_r($response);exit;

        }
    }

    public function smsWebhook(Request $request){
        $requestData = $request->all();
        Log::info($requestData);
        SmsWebhook::dispatch($requestData);
        return response()->json(['message' => 'Successfully!']);
    }
}