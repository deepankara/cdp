<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SendgridController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\SmsAnalytics;
use Auth;
use Str;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;
use finfo;


class ApiController extends BaseController
{
    public function sendMessage(Request $request){
        $requestData = $request->all();
        $isSendMessage = false;
        $apiId = Str::random(15);

        $validator = Validator::make($request->all(), [
            'channel' => 'required|string',
            'source' => 'required|string',
        ]);

        if($validator->fails()) {
            return response()->json(['Bad Request'], 400);
        }
        

        // $json = [];
        // if(isset($request['channel']) && $requestData['channel'] == ''){
        //     return response()->json(['error' => 'Invalid channel.'], 400);
        // }

        // if($requestData['channel'] == 'email'){

        //     $validator = Validator::make($request->all(), [
        //         'email' => 'required|email',
        //         'name' => 'required|string',
        //         'channel' => 'required|string',
        //         'source' => 'required|string',
        //         'type' => 'required|string',
        //         'email_subject' => 'required|string',
        //         'email_from_name' => 'required|string',
        //         'template_id' => 'required|integer',
        //     ]);
    
        //     if ($validator->fails()) {
        //         return response()->json(['Bad Requests'], 400);
        //     }

        //     if (!isset($requestData['template_id']) || !is_numeric($requestData['template_id'])) {
        //         return response()->json(['error' => 'Invalid template ID.'], 400);
        //     }
        //     $template = DB::table('templates')->whereId($requestData['template_id'])->get()->toArray();
        //     $template = (array) current($template);
        //     if(isset($template['html_content']) && $template['html_content'] != ''){
        //         $emailScopes = $request->user()->email_scopes;
        //         if(!in_array($template['id'],$emailScopes)){
        //             return response()->json(['error' => 'Please Contact Administrator'], 400);
        //         }
        //         $html_content = $template['html_content'];
        //         if(preg_match('/{{\s*(?!unsubscribe\b)[\w\.]+\s*}}/', $html_content)) {
        //             if(isset($requestData['template_attr']) && $requestData['template_attr'] != ''){
        //                 $attributes = $requestData['template_attr'];
        //                 $attributes = array_merge($requestData,$attributes);
        //                 $unsubscribeUrl = env('EMAIL_UNSUB_URL');
        //                 $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
        //                 $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
        //                     $key = $matches[1]; 
        //                     return $attributes[$key] ?? $matches[0]; 
        //                 }, $html_content);
        //                 $attributes['html_content'] = $customerHtmlContent; 
        //                 $attributes['email_subject'] =  preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
        //                     $key = $matches[1]; 
        //                     return $attributes[$key] ?? $matches[0]; 
        //                 }, $attributes['email_subject']);
        //             }else{
        //                 return response()->json(['error' => 'Template Attributes is missing'], 400);
        //             }
        //         }else{
        //             $attributes = $requestData;
        //             $unsubscribeUrl = env('EMAIL_UNSUB_URL');
        //             $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
        //             $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
        //                 $key = $matches[1]; 
        //                 return $attributes[$key] ?? $matches[0]; 
        //             }, $html_content);
        //             $attributes['html_content'] = $customerHtmlContent;    
        //         }

       
                

                
        //         if (preg_match('/{{\s*[\w\.]+\s*}}/', $attributes['html_content'])) {
        //             return response()->json(['error' => 'Template Variables Missing.'], 400);
        //         }

        //         $provider = DB::table('channels')->where('source','Email')->value('provider');
        //         if($provider == "SES"){
        //             $isSendMessage = EmailController::sendAmazonSes($attributes,$attributes);
        //         }else{
        //             $isSendMessage = EmailController::sendTelSpielEmail($attributes,$attributes);
        //         }
        //         // $isSendMessage = EmailController::sendTelSpielEmail($attributes,$attributes);
        //     }else{
        //         return response()->json(['error' => 'Template not found.'], 404);
        //     }
        //     // $isSendMessage = SendgridController::sendGridEmail($attributes,$attributes);
        // }
        if($requestData['channel'] == 'email'){

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required|string',
                'channel' => 'required|string',
                'source' => 'required|string',
                'type' => 'required|string',
                'email_subject' => 'required|string',
                'email_from_name' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['Bad Request'], 400);
            }

            
            if($requestData['type'] == 'text'){
                $attributes = $requestData;
                $attributes['template_id'] = "text"; 
            }else{
                if (!isset($requestData['template_id']) || !is_numeric($requestData['template_id'])) {
                    return response()->json(['error' => 'Invalid template ID.'], 400);
                }
                $template = DB::table('templates')->whereId($requestData['template_id'])->get()->toArray();
                $template = (array) current($template);
                if(isset($template['html_content']) && $template['html_content'] != ''){
                    $emailScopes = $request->user()->email_scopes;
                    if(!in_array($template['id'],$emailScopes)){
                        return response()->json(['error' => 'Please Contact Administrator'], 400);
                    }
                    $html_content = $template['html_content'];
                    if(preg_match('/{{\s*(?!unsubscribe\b)[\w\.]+\s*}}/', $html_content)) {
                        if(isset($requestData['template_attr']) && $requestData['template_attr'] != ''){
                            $attributes = $requestData['template_attr'];
                            $attributes = array_merge($requestData,$attributes);
                            $unsubscribeUrl = env('EMAIL_UNSUB_URL');
                            $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
                            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                                $key = $matches[1]; 
                                return $attributes[$key] ?? $matches[0]; 
                            }, $html_content);
                            $attributes['html_content'] = $customerHtmlContent;    
                        }else{
                            return response()->json(['error' => 'Template Attributes is missing'], 400);
                        }
                    }else{
                        $attributes = $requestData;
                        $unsubscribeUrl = env('EMAIL_UNSUB_URL');
                        $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
                        $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                            $key = $matches[1]; 
                            return $attributes[$key] ?? $matches[0]; 
                        }, $html_content);
                        $attributes['html_content'] = $customerHtmlContent;    
                        $attributes['email_subject'] =  preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                            $key = $matches[1]; 
                            return $attributes[$key] ?? $matches[0]; 
                        }, $attributes['email_subject']);
                    }
                    if (preg_match('/{{\s*[\w\.]+\s*}}/', $attributes['html_content'])) {
                        return response()->json(['error' => 'Template Variables Missing.'], 400);
                    }
                }else{
                    return response()->json(['error' => 'Template not found.'], 404);
                }
            } 
            
            
            if (!empty($requestData['attachment'])) {
                $attachment = $requestData['attachment'];
                if (!empty($attachment['type']) && !empty($attachment['content'])) {
                    if ($attachment['type'] === 'base64') {
                        $file = $attachment['content'];
                        $fileData = base64_decode(preg_replace('/^data:[^;]+;base64,/', '', $file));
                        // Calculate size using the formula
                        $maxSizeInBytes = 10 * 1024 * 1024;
                        $sizeInBytes = (strlen($fileData) * 3 / 4) - substr_count($fileData, '=');
                        if ($sizeInBytes > $maxSizeInBytes) {
                            return response()->json(['error' => 'File size exceeds the maximum limit of 10MB.'], 400);
                        }
                        if ($fileData === false) {
                            return response()->json(['error' => 'Invalid file data'], 400);
                        }
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->buffer($fileData);
                        $extensions = config('filetypes.extensions');
                        $extension = $extensions[$mimeType] ?? 'bin'; // Default to .bin if unknown
                        if($extension == "bin"){
                            return response()->json(['error' => 'File Type not Supported.Contact Application Owner'], 400);
                        }
                        if(isset($attachment['file_name']) && $attachment['file_name'] != ''){
                            $fileName = $attachment['file_name'] . '.' . $extension;
                        }else{
                            $fileName = uniqid() . '.' . $extension;
                        }
                        $attributes['file'] = $fileData;
                        $attributes['file_name'] = $fileName;
                    } else {

                    }
                }else{
                    return response()->json(['error' => 'Invalid attachment data.'], 400);
                }
            } // Handle other types if needed
           

            $provider = DB::table('channels')->where('source','Email')->value('provider');
            if($provider == "SES"){
                $isSendMessage = EmailController::sendAmazonSes($attributes,$attributes);
                if(isset($isSendMessage['message_id']) && $isSendMessage['message_id'] != ''){
                    $apiId = $isSendMessage['message_id'];
                    $isSendMessage = true;
                }
            }else{
                $isSendMessage = EmailController::sendTelSpielEmail($attributes,$attributes);
            }
                // $isSendMessage = EmailController::sendTelSpielEmail($attributes,$attributes);
            
            // $isSendMessage = SendgridController::sendGridEmail($attributes,$attributes);
        }

        if($requestData['channel'] == 'sms'){
            $template = DB::table('sms_templates')->whereId($requestData['template_id'])->get()->toArray();
            $template = (array) current($template);

            if(isset($template['dlt_template_id']) && $template['dlt_template_id'] != ''){
                $smsScopes = $request->user()->sms_scopes;
                if(!in_array($template['id'],$smsScopes)){
                    return response()->json(['error' => 'Please Contact Administrator'], 400);
                }
                $dltTemplateId = $template['dlt_template_id'];
                $html_content = $template['sms'];
                $customerHtmlContent = $html_content;
                $stringCount = substr_count($html_content,"{{");
                if($stringCount >= 1){
                    if(isset($requestData['template_attr']) && $requestData['template_attr'] != ''){
                        $attributes = $requestData['template_attr'];
                    }else{
                        return response()->json(['error' => 'Template Attributes is missing'], 400);
                    }
                    if(count($attributes) != $stringCount){
                        return response()->json(['error' => 'Template variables missing'], 400);
                    }
                    $attributes = array_merge($requestData,$attributes);
                    $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                        $key = $matches[1]; 
                        return $attributes[$key] ?? $matches[0]; 
                    }, $html_content);
                    $stringCount = substr_count($customerHtmlContent,"{{");
                    if($stringCount >= 1){
                        return response()->json(['error' => 'Template Variable Missing'], 400);
                    }
                }
                $isSendMessage = SmsController::sendSms($customerHtmlContent,$requestData['mobile_no'],$requestData['template_id'],$dltTemplateId);
            }else{
                return response()->json(['error' => 'Invalid Template'], 400);
            }
        }

        if($requestData['channel'] == 'whatsapp') {

            $validator = Validator::make($request->all(), [
                'mobile_no' => 'required',
                'channel' => 'required|string',
                'source' => 'required|string',
                'template_id' => 'required|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json(['Bad Request'], 400);
            }

            $component = [];
            $template = DB::table('whatsapp_templates')->where("name",$requestData['template_id'])->get()->toArray();
            $template = (array) current($template);
            if(!isset($template['id'])){
                return response()->json(['error' => 'Template Not Found'], 400);
            }
            $whatsappScopes = $request->user()->whatsapp_scopes;
            if(!in_array($template['id'],$whatsappScopes)){
                return response()->json(['error' => 'Please Contact Administrator'], 400);
            }

            $mobileLast10 = substr($requestData['mobile_no'], -10);

            // if($request->user()->source == "salesforce"){
            //     $dontCheckOtpIn = false;
            //     if($template['name'] == 'aux_opt_in'){
            //         $dontCheckOtpIn = true;
            //     }

            //     if(!$dontCheckOtpIn){
            //         $checkWhatsappUnsubscribe = DB::table('whatsapp_opt_in')->where(DB::raw('RIGHT(number, 10)'), $mobileLast10)->count();
            //         if($checkWhatsappUnsubscribe < 1){
            //             return response()->json(['status'=>false,'reason'=>'User has not opted yet']);
            //         }
            //     }

            // if($dontCheckOtpIn){
                $checkWhatsappUnsubscribe = DB::table('whatsapp_unsubscribe')->where(DB::raw('RIGHT(number, 10)'), $mobileLast10)->count();
                if($checkWhatsappUnsubscribe >= 1){
                    return response()->json(['error' => 'User has opted out from communications'], 400);
                }
            // }
            // }
        try{

            if(isset($template['category']) && $template['category'] == "AUTHENTICATION"){
                if(isset($requestData['template_attr']['otp']) && $requestData['template_attr']['otp'] != ''){
                    $otpCode = $requestData['template_attr']['otp'];
                }else{
                    return response()->json(['error' => 'Bad Request'], 400);
                }


                $component = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otpCode
                            ]
                        ]
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => "0",
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otpCode
                            ]
                        ]
                    ]
                ];
                
            }else{
                if (isset($template['header_type']) && $template['header_type'] != 'NONE') {
                        $header = [];
                        $header['type'] = "HEADER";
                        $header['parameters'] = [];

                        if(!$requestData['template_attr']['header_value']['value']){
                            return response()->json(['error' => 'Header Value is missing'], 400);
                        }

                        if($template['header_type'] == "VIDEO"){
                            $header['parameters'][0] = [
                                "type" => "video",
                                "video" => ['link'=>$requestData['template_attr']['header_value']['value']]
                            ];
                        }

                        if($template['header_type'] == "DOCUMENT"){
                            $header['parameters'][0] = [
                                "type" => "document",
                                "document" => ['link'=>$requestData['template_attr']['header_value']['value']]
                            ];
                        }

                        if($template['header_type'] == "IMAGE"){
                            $header['parameters'][0] = [
                                "type" => "image",
                                "image" => ['link'=>$requestData['template_attr']['header_value']['value']]
                            ];
                        }

                        if($template['header_type'] == "TEXT"){
                            if(str_contains($template['header_name'],"{{1}}")){
                                $header['parameters'][0] = [
                                    "type" => "text",
                                    "text" => $requestData['template_attr']['header_value']['value']
                                ];
                            }
                        }
                        $component[] = $header;

                }

                if (isset($requestData['template_attr']['body_value']) && !empty($requestData['template_attr']['body_value'])) {
                    $body = [];
                    $body['type'] = "BODY";
                    $body['parameters'] = [];
            
                    if(!isset($requestData['template_attr']['body_value'])){
                        return response()->json(['error' => 'Body Value is missing'], 400);
                    }
                    // Loop through the body_value array and create parameter elements
                    foreach ($requestData['template_attr']['body_value'] as $index => $text) {
                        if($text == ''){
                            // return response()->json(['error' => 'Body Value is missing'], 400);
                        }
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
                            $button['parameters']['text'] = $requestData['template_attr']['button']['button_dynamic_url'];
                            $button['parameters'] = array($button['parameters']);
                            $component[] = $button;
                        }
                    }
                }
            }
        }catch (\Exception $e) {
            return response()->json(['error' => "Invalid Payload"], 400);

        }

            
            
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
                        'source' => isset($requestData['source']) && $requestData['source'] != '' ? $requestData['source'] : null,
                        'template_id' => $requestData['template_id'],
                        'api_id' => $apiId,
                    ];
                }
                $isSendMessage = DB::table("whatsapp_analytics")->insert($whatsappData);
            }else{
                if(isset($response['error']['message']) && $response['error']['message'] != ''){
                    return response()->json(['error' => $response['error']['message']], 400);
                }
            }
        }
        $json['email'] = isset($requestData['email']) && $requestData['email'] != '' ? $requestData['email'] : null;
        $json['mobile_no'] = isset($requestData['mobile_no']) && $requestData['mobile_no'] != '' ? $requestData['mobile_no'] : null;
        $json['client_id'] = $request->user()->id;
        $json['channel'] = $requestData['channel'];
        $json['endpoint'] = $request->url();
        $json['status'] = $isSendMessage;
        $json['payload'] = json_encode($requestData);
        $json['created_at'] = Carbon::now();
        DB::table('api_logs')->insert($json);
        if(!$isSendMessage){
            return response()->json(['message' => 'Communication processed unsuccessfully.'], 400);
        }else{
            return response()->json(['message' => 'Communication processed successfully.','ref_id'=>$apiId]);
        }
        return $isSendMessage;
    }
}