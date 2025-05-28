<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RulesController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\SmsAnalytics;
use Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Session;
use App\Jobs\EmailWebhook;
use App\Jobs\AmazonSesEmailWebhook;
use Illuminate\Support\Facades\Mail;



class EmailController extends BaseController
{
    public function convertToIST($timestamp) {
        $dateTime = Carbon::createFromTimestamp($timestamp);
        $dateTime->setTimezone('Asia/Kolkata');
        return $dateTime->format('Y-m-d H:i:s');
    }

    public function sendEmail(Request $request){
        $campaign = DB::table('campaign')->select('campaign.include_segment_id',
        'campaign.rule_id','campaign.template_id','campaign.schedule',
        'templates.html_content',
        'campaign.email_subject',
        'campaign.email_from_name','campaign.id as campaign_id')->leftjoin('templates','templates.id','campaign.template_id')
        ->orderBy('campaign_id','desc')
        ->where('campaign.channel','Email')
        ->get()->toArray();
        $campaign = (array) current($campaign);


        $customers = DB::table('customers')->where('segment_id',$campaign['include_segment_id']);
        if(isset($campaign['rule_id']) && $campaign['rule_id'] != '') {
            $customers = RulesController::rulesSync($campaign['rule_id'],$customers);
        }
        
        $customers = $customers->select('customers.*','email_unsubscribe.email_id')->leftJoin('email_unsubscribe','customers.email', '=','email_unsubscribe.email_id')
                                ->whereNull('email_unsubscribe.email_id');
        
        $customers = $customers->get()->toArray();
        
        $html_content = $campaign['html_content'];
        foreach($customers as $key => $value){
            $emailArray = $this->validateEmail($value->email,$campaign['include_segment_id']);

            $convertArray = (array) $value;
            $attributes = json_decode($value->attributes,true);
            $attributes = array_merge($convertArray,$attributes);
            // $unsubscribeUrl = 'https://example.com/unsubscribe?email=' . urlencode($attributes['email']);
            // $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            $value->html_content = $customerHtmlContent;
            $this->sendTelSpielEmail($campaign,$value);
            if($key == 0){
                DB::table('campaign')->whereId($campaign['campaign_id'])->update(['campaign_executed'=>true]);
            }
        }
    }

    // public static function sendAmazonSes($campaign,$customers){
    //     if(is_object($customers)){
    //         $customers = (array) $customers;
    //     }

    //     if(isset($customers['template_id']) && $customers['template_id'] != ''){
    //         $campTempId = $customers['template_id'];
    //         $campTempkey = 'TEMPLATEID';
    //         $dbKey = 'template_id';
    //         $type = "TEMPLATE";
    //     }else{
    //         $campTempId = $campaign['campaign_id'];
    //         $campTempkey = 'CAMPAIGNID';
    //         $dbKey = 'campaign_id';
    //         $type = "CAMPAIGN";
    //     }
    //     $strRandom = Str::random(10);
    //     $fromEmailAddress = env("MAIL_FROM_ADDRESS");

    //     $mail = Mail::html($customers['html_content'], function ($message) use ($fromEmailAddress,$campaign,$customers,$campTempkey,$campTempId,$strRandom) {
    //         $message->from($fromEmailAddress, $campaign['email_from_name'])
    //         ->to($customers['email'], $customers['name'])
    //         ->subject($campaign['email_subject'])
    //         ->getHeaders()
    //         ->addTextHeader('x-ses-configuration-set', 'DICP-SES2SNS-EVENT-CONFIG-SET')
    //         ->addTextHeader('unique-id', $strRandom)
    //         ->addTextHeader('X-SES-MESSAGE-TAGS', "{$campTempkey}={$campTempId}")
    //         ->addTextHeader('List-Unsubscribe', '<unsubscribe>');
    //     });

     
    //     if($mail){
    //         $headers = $mail->getOriginalMessage()->getHeaders();
    //         $sesMessageId = $headers->get('X-SES-Message-ID') ? $headers->get('X-SES-Message-ID')->getBodyAsString() : null;
    
    //         $insertData[$dbKey] = $campTempId;
    //         $insertData['sg_message_id'] = $sesMessageId;
    //         $insertData['event'] = "processed";
    //         $insertData['email'] = $customers['email'];
    //         $insertData['indian_time'] = Carbon::now();
    //         return DB::table('email_analytics')->insert($insertData);
    //     }else{
    //         $strRandom = Str::random();
    //         $insertData = [
    //             [
    //                 $dbKey => $campTempId,
    //                 'sg_message_id' => $strRandom,
    //                 'event' => 'processed',
    //                 'email' => $customers['email'],
    //                 'indian_time' => Carbon::now(),
    //             ],
    //             [
    //                 $dbKey => $campTempId,
    //                 'sg_message_id' => $strRandom,
    //                 'event' => 'failed',
    //                 'email' => $customers['email'],
    //                 'indian_time' => Carbon::now(),
    //             ]
    //         ];
    //         return false;
    //     }
    // }
    public static function sendAmazonSes($campaign,$customers){
        if(is_object($customers)){
            $customers = (array) $customers;
        }
        $textOrHtml = 'html';
        if(isset($customers['template_id']) && $customers['template_id'] != ''){
            $campTempId = $customers['template_id'];
            $campTempkey = 'TEMPLATEID';
            $dbKey = 'template_id';
            $type = "TEMPLATE";
            if($customers['template_id'] == "text"){
                $textOrHtml = 'raw';
                $textOrHtmlContent = $customers['content'];
                $insertData['text_content'] = $textOrHtmlContent;
            }else{
                $textOrHtmlContent = $customers['html_content'];
            }
        }else{
            $campTempId = $campaign['campaign_id'];
            $campTempkey = 'CAMPAIGNID';
            $dbKey = 'campaign_id';
            $type = "CAMPAIGN";
            $textOrHtmlContent = $customers['html_content'];
        
        }
        $strRandom = Str::random(10);
        $fromEmailAddress = env("MAIL_FROM_ADDRESS");
        $mail = Mail::$textOrHtml($textOrHtmlContent, function ($message) use ($fromEmailAddress,$campaign,$customers,$campTempkey,$campTempId,$strRandom) {
            $message->from($fromEmailAddress, $campaign['email_from_name'])
            ->to($customers['email'], $customers['name'])
            ->subject($campaign['email_subject'])
            ->getHeaders()
            ->addTextHeader('x-ses-configuration-set', 'DICP-SES2SNS-EVENT-CONFIG-SET')
            ->addTextHeader('unique-id', $strRandom)
            ->addTextHeader('X-SES-MESSAGE-TAGS', "{$campTempkey}={$campTempId}")
            ->addTextHeader('List-Unsubscribe', '<unsubscribe>');

            if (!empty($campaign['file']) && !empty($campaign['file_name'])) {
                $message->attachData($campaign['file'], $campaign['file_name']);
            }
        });


     
        if($mail){
            $headers = $mail->getOriginalMessage()->getHeaders();
            $sesMessageId = $headers->get('X-SES-Message-ID') ? $headers->get('X-SES-Message-ID')->getBodyAsString() : null;
    
            $insertData[$dbKey] = $campTempId;
            $insertData['sg_message_id'] = $sesMessageId;
            $insertData['event'] = "processed";
            $insertData['email'] = $customers['email'];
            $insertData['indian_time'] = Carbon::now();
            if($type == "CAMPAIGN"){
                return DB::table('email_analytics')->insert($insertData);
            }else{
                DB::table('email_analytics')->insert($insertData);
                return ['status'=>true,'message_id'=>$sesMessageId];
            }
        }else{
            $strRandom = Str::random();
            $insertData = [
                [
                    $dbKey => $campTempId,
                    'sg_message_id' => $strRandom,
                    'event' => 'processed',
                    'email' => $customers['email'],
                    'indian_time' => Carbon::now(),
                ],
                [
                    $dbKey => $campTempId,
                    'sg_message_id' => $strRandom,
                    'event' => 'failed',
                    'email' => $customers['email'],
                    'indian_time' => Carbon::now(),
                ]
            ];
            return false;
        }
    }

    public static function validateEmail($email,$segmentId){
        $checkCount = DB::table('email_dump')->where('segment_id',$segmentId)->where('email',$email)->get()->toArray();
        $dirtyOrClean = 'clean';
        if(count($checkCount) < 1){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.listclean.xyz/v1/verify/email/'.$email,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'X-AUTH-TOKEN: '.env("LISTCLEAN_API_KEY")
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response,true);
            if(isset($response['data']['status']) && $response['data']['status'] != ''){
                $dumpEmail = [];
                $dumpEmail['email'] = $response['data']['email'];
                $dumpEmail['status'] = $response['data']['status'];
                $dumpEmail['reason'] = $response['data']['remarks'];
                $dumpEmail['segment_id'] = $segmentId;
                $dumpEmail['created_at'] = Carbon::now();
                DB::table('email_dump')->insert($dumpEmail);
                if($dumpEmail['status'] == "dirty"){
                    $dirtyOrClean = 'dirty';
                }
            }
        }else{
            $checkCount = (array) current($checkCount);
            if($checkCount['status'] == "dirty"){
                $dirtyOrClean = 'dirty';
            }
        }
        return $dirtyOrClean;
    }

    public static function sendTelSpielEmail($campaign,$customers){
        if(is_object($customers)){
            $customers = (array) $customers;
        }

        if(is_object($campaign)){
            $campaign = (array) $campaign;
        }

        if(isset($customers['template_id']) && $customers['template_id'] != ''){
            $campTempId = $customers['template_id'];
            $campTempkey = 'TEMPLATEID';
            $dbKey = 'template_id';
            $type = "TEMPLATE";
        }else{
            $campTempId = $campaign['campaign_id'];
            $campTempkey = 'CAMPAIGNID';
            $dbKey = 'campaign_id';
            $type = "CAMPAIGN";
        }

        $sendGridFromName = env('TELSPIEL_FROM_EMAIL');
        $telSpielKey = env("TELSPIEL_KEY");
        $emailPayload = [
            "apiver" => "1.0",
            "email" => [
                "ver" => "1.0",
                "dlr" => [
                    "url" => env("TELSPIEL_WEBHOOK_URL")
                ],
                "messages" => [
                    [
                        "addresses" => [
                            [
                                "to" => [
                                    [
                                        "emailid" => $customers['email'],
                                        "name" => $customers['name']
                                    ]
                                ],
                                "subject" => $campaign['email_subject'],
                                "user_custom_args" =>  [
                                    $campTempkey => $campTempId,
                                    "TYPE" => $type
                                ],
                            ]
                        ],
                        "subject" => $campaign['email_subject'],
                        "content" => [
                            [
                                "type" => "text/html",
                                "value" => $customers['html_content']
                            ]
                        ],
                        "from" => [
                            "emailid" => $sendGridFromName,
                            "name" => $campaign['email_from_name']
                        ],
                        "category" => "Test"
                    ]
                ]
            ]
        ];

        if(isset($customers['retarget_campaign_id']) && $customers['retarget_campaign_id'] != ''){
            $emailPayload['email']['messages'][0]['addresses'][0]['user_custom_args']['retarget_campaign_id'] = $customers['retarget_campaign_id'];
        }



        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.goinfinito.com/unified/v2/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($emailPayload),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$telSpielKey.'',
                'Content-Type: application/json',
            ),
        ));


        $response = curl_exec($curl);
        $response = json_decode($response,true);
        Log::info($response);
        if(isset($response['statuscode']) && $response['statuscode'] == 200){
            if(isset($response['messageack']['guids']) && $response['messageack']['guids']){
                $response = $response['messageack']['guids'];
                $insertData = [];
                foreach($response as $key => $value){
                    if(isset($customers['retarget_campaign_id']) && $customers['retarget_campaign_id'] != ''){
                        $insertData[$key]['retarget_campaign_id'] = $customers['retarget_campaign_id'];
                    }
                    $insertData[$key][$dbKey] = $campTempId;
                    $insertData[$key]['sg_message_id'] = $value['guid'];
                    $insertData[$key]['event'] = "processed";
                    $insertData[$key]['email'] = $customers['email'];
                    $insertData[$key]['indian_time'] = $value['submitdate'];
                }
                return DB::table('email_analytics')->insert($insertData);
            }
        }

        $strRandom = Str::random();
        $insertData = [
            [
                $dbKey => $campTempId,
                'sg_message_id' => $strRandom,
                'event' => 'processed',
                'email' => $customers['email'],
                'indian_time' => Carbon::now(),
            ],
            [
                $dbKey => $campTempId,
                'sg_message_id' => $strRandom,
                'event' => 'failed',
                'email' => $customers['email'],
                'indian_time' => Carbon::now(),
            ]
        ];
        if(isset($customers['retarget_campaign_id']) && $customers['retarget_campaign_id'] != ''){
            $insertData[0]['retarget_campaign_id'] = $customers['retarget_campaign_id'];
            $insertData[1]['retarget_campaign_id'] = $customers['retarget_campaign_id'];
        }
        return DB::table('email_analytics')->insert($insertData);
    }

    

    
    
    public function telSpielEmailWebhook(Request $request){
        $requestData = $request->all();
        Log::info($requestData);
        EmailWebhook::dispatch($requestData);
        return response()->json(['message' => 'Successfully!']);
        
        // $requestData = $request->all();
        
        // Log::info($requestData);
        // $insertData = [];
        // if(isset($requestData['custom_fields']['user_custom_args']['TYPE']) && $requestData['custom_fields']['user_custom_args']['TYPE'] == 'CAMPAIGN'){
        //     $insertData['campaign_id'] = $requestData['custom_fields']['user_custom_args']['CAMPAIGNID'];
        // }else{
        //     $insertData['template_id'] = $requestData['custom_fields']['user_custom_args']['TEMPLATEID'];
        // }
        // $insertData['email'] = $requestData['email_id'];
        // $insertData['event'] = $requestData['event'];
        // $insertData['sg_message_id'] = $requestData['custom_fields']['guid'];
        // $insertData['indian_time'] = $requestData['timestamp'];
        // $insertData['ip_address'] = (isset($requestData['ip']) && $requestData['ip'] != '') ? $requestData['ip'] : '';
        
        // if(isset($requestData['useragent']) && $requestData['useragent'] != ''){
        //     $insertData['user_agent'] = $requestData['useragent'];
        //     $agent = new Agent();
        //     $agent->setUserAgent($requestData['useragent']);
        //     $device = (isset($requestData['device']) && $requestData['device'] != '') ? $requestData['device'] : '';
        //     $platform = $agent->platform();
        //     $browser = $agent->browser();
        //     $insertData['device'] = $device;
        //     $insertData['platform'] = $platform;
        //     $insertData['browser'] = $browser;
        // }

        // if($requestData['event'] == 'click'){
        //     $insertData['click_url'] = (isset($requestData['url']) && $requestData['url'] != '') ? $requestData['url'] : '';
        //     $ip = $requestData['ip'];
        //     $response = Http::get("http://ipinfo.io/{$ip}/json");
        //     if($response->successful()) {
        //         $data = $response->json();
        //         $insertData['city'] = $data['city'];
        //         $insertData['region'] = $data['region'];
        //         $insertData['country'] = $data['country'];
        //         $insertData['postal'] = $data['postal'];
        //     }
        // }
        // DB::table('email_analytics')->insert($insertData);
    }

    public function emailWebhook(Request $request){
        $requestData = $request->json()->all();
        Log::info($requestData);
        AmazonSesEmailWebhook::dispatch($requestData);
        return response()->json(['message' => 'Successfully!']);
        // $insertData = [];
        // $tableName = 'email_analytics';
        // foreach($requestData as $key => $value){
        //     if(isset($value['custom_fields']) && $value['type'] == "transactional"){
        //         // $tableName = 'email_analytics_api';
        //         $insertData[$key]['template_id'] = $value['template_id'];

        //     }else{
        //         $insertData[$key]['retarget_campaign_id'] = (isset($value['retargeting_campaign_id']) && $value['retargeting_campaign_id'] != '') ? $value['retargeting_campaign_id'] : '';
        //         $insertData[$key]['campaign_id'] = $value['campaign_id'];
        //     }
        //     $insertData[$key]['email'] = $value['email'];
        //     $insertData[$key]['event'] = $value['event'];
        //     $insertData[$key]['sg_event_id'] = $value['sg_event_id'];
        //     $insertData[$key]['sg_message_id'] = $value['sg_message_id'];
        //     $insertData[$key]['timestamp'] = $value['timestamp'];
        //     $insertData[$key]['indian_time'] = $this->convertToIST($value['timestamp']);
        //     $insertData[$key]['ip_address'] = (isset($value['ip']) && $value['ip'] != '') ? $value['ip'] : '';
        //     if(isset($value['useragent']) && $value['useragent'] != ''){
        //         $insertData[$key]['user_agent'] = $value['useragent'];
        //         $agent = new Agent();
        //         $agent->setUserAgent($value['useragent']);
        //         $device = $agent->device();
        //         $platform = $agent->platform();
        //         $browser = $agent->browser();
        //         $insertData[$key]['device'] = $device;
        //         $insertData[$key]['platform'] = $platform;
        //         $insertData[$key]['browser'] = $browser;
        //     }

        //     $insertData[$key]['user_agent'] = (isset($value['useragent']) && $value['useragent'] != '') ? $value['useragent'] : '';
        //     if($value['event'] == 'click'){
        //         $insertData[$key]['click_url'] = (isset($value['url']) && $value['url'] != '') ? $value['url'] : '';
        //         $ip = $value['ip'];
        //         $response = Http::get("http://ipinfo.io/{$ip}/json");
        //         if($response->successful()) {
        //             $data = $response->json();
        //             $insertData[$key]['city'] = $data['city'];
        //             $insertData[$key]['region'] = $data['region'];
        //             $insertData[$key]['country'] = $data['country'];
        //             $insertData[$key]['postal'] = $data['postal'];
        //         }
        //     }
        // }
        // DB::table($tableName)->insert($insertData);
    }

    public function emailRetargetting(Request $request){

        $startTime = now()->subMinutes(10); // Check only last 5 minutes

        $fiveMinutesAgo = Carbon::now()->subMinutes(40)->format('Y-m-d H:i');
        $now = Carbon::now()->format('Y-m-d H:i');
        
        $campaign = DB::table('retarget_campaign')
                    ->whereRaw("
                        JSON_SEARCH(retarget_campaign.retarget, 'one', '%open%', NULL, '$[*].when') IS NOT NULL
                        AND EXISTS (
                            SELECT 1 FROM JSON_TABLE(
                                retarget_campaign.retarget, 
                                '$[*]' COLUMNS (
                                    schedule VARCHAR(16) PATH '$.schedule'
                                )
                            ) AS jt
                            WHERE LEFT(jt.schedule, 16) BETWEEN ? AND ?
                        )
                    ", [$fiveMinutesAgo, $now])
                    ->where('is_click_executed', false)
                    ->orderBy('retarget_campaign.id', 'desc')
                    ->get()
                    ->toArray();
    
        
        echo "<pre>";print_r($campaign);exit;
       
        $retargetCampaign = DB::table('retarget_campaign')
        ->leftJoin('campaign', 'campaign.id', '=', 'retarget_campaign.campaign_id')
        ->select('campaign.include_segment_id', 'campaign.rule_id', 'campaign.template_id', 'retarget_campaign.*')
        ->where(DB::raw("JSON_SEARCH(retarget_campaign.retarget, 'one', '%open%', NULL, '$[*].when')"), 'IS NOT', DB::raw('NULL'))
        ->orderBy('retarget_campaign.id','desc')
        ->get()
        ->map(function ($campaign) {
            $retarget = json_decode($campaign->retarget, true);
            $openedEntries = array_filter($retarget, function ($item) {
                return isset($item['when']) && $item['when'] === "opened";
            });
            $campaign->retarget = $openedEntries;
            return $campaign;
        })->toArray();

        $retargetCampaign = (array) current($retargetCampaign); 
        $campaignId = $retargetCampaign['campaign_id'];
        $deliveredButNotOpenedEmails = DB::table('email_analytics as ea')
            ->leftJoin('customers as c', 'ea.email', 'c.email')
            ->where('ea.campaign_id', $campaignId)
            ->where('c.segment_id',$retargetCampaign['include_segment_id'])
            ->where('ea.event', 'delivered')
            ->whereNotExists(function($query) use ($campaignId) {
                $query->select(DB::raw(1))
                    ->from('email_analytics as sub')
                    ->where('sub.campaign_id', $campaignId)
                    ->where('sub.event', 'open')
                    ->whereRaw('sub.email = ea.email'); 
            })
            ->select('ea.email', 'ea.campaign_id', 'ea.event', 'c.attributes','c.segment_id')
            ->groupBy('ea.email') 
            ->get()
            ->toArray();

        $retargetArray = current($retargetCampaign['retarget']);
        $html_content = $retargetArray['html_content'];
        
        foreach($deliveredButNotOpenedEmails as $key => $value){
            $convertArray = (array) $value;
            $attributes = json_decode($value->attributes,true);
            $attributes = array_merge($convertArray,$attributes);
            $unsubscribeUrl = 'https://example.com/unsubscribe?email=' . urlencode($attributes['email']);
            $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            $value->html_content = $customerHtmlContent;
            $retargetCampaign['retarget'] = current($retargetCampaign['retarget']);            
            $retargetCampaign['is_retarget'] = 'yes';
            
            $this->sendTelSpielEmail($retargetCampaign,$value);
            exit;
            // if($key == 0){
            //     DB::table('campaign')->whereId($campaign['campaign_id'])->update(['campaign_executed'=>true]);
            // }
        }
    }

    public function emailRetargettingOpen(Request $request){
       
        $retargetCampaign = DB::table('retarget_campaign')
        ->leftJoin('campaign', 'campaign.id', '=', 'retarget_campaign.campaign_id')
        ->select('campaign.include_segment_id', 'campaign.rule_id', 'campaign.template_id', 'retarget_campaign.*')
        ->where(DB::raw("JSON_SEARCH(retarget_campaign.retarget, 'one', '%click%', NULL, '$[*].when')"), 'IS NOT', DB::raw('NULL'))
        ->orderBy('retarget_campaign.id','desc')
        ->get()
        ->map(function ($campaign) {
            $retarget = json_decode($campaign->retarget, true);
            $openedEntries = array_filter($retarget, function ($item) {
                return isset($item['when']) && $item['when'] === "clicked";
            });
            $campaign->retarget = $openedEntries;
            return $campaign;
        })->toArray();

        $retargetCampaign = (array) current($retargetCampaign);
        
        $campaignId = $retargetCampaign['campaign_id'];
        
        $retargetArray = current($retargetCampaign['retarget']);
        // $deliveredButNotOpenedEmails = DB::table('email_analytics as ea')
        //     ->leftJoin('customers as c', 'ea.email', 'c.email')
        //     ->where('ea.campaign_id', $campaignId)
        //     ->where('c.segment_id',$retargetCampaign['include_segment_id'])
        //     ->where('ea.event', 'delivered')
        //     ->get()->toArray();

        $deliveredButNotOpenedEmails = DB::table('email_analytics as ea')
            ->leftJoin('customers as c', 'ea.email', 'c.email')
            ->where('ea.campaign_id', $campaignId)
            ->where('c.segment_id',$retargetCampaign['include_segment_id'])
            ->where('ea.event', 'delivered')
            ->whereNotExists(function($query) use ($campaignId,$retargetArray) {
                $query->select(DB::raw(1))
                    ->from('email_analytics as sub')
                    ->where('sub.campaign_id', $campaignId)
                    ->where('sub.event', 'click')
                    ->where('sub.click_url',$retargetArray['click_link'])
                    ->whereRaw('sub.email = ea.email'); 
            })
            ->select('ea.email', 'ea.campaign_id', 'ea.event', 'c.attributes','c.segment_id')
            ->groupBy('ea.email') 
            ->get()
            ->toArray();
        
            echo "<pre>";print_r($deliveredButNotOpenedEmails);exit;

        $html_content = $retargetArray['html_content'];
        
        foreach($deliveredButNotOpenedEmails as $key => $value){
            $convertArray = (array) $value;
            $attributes = json_decode($value->attributes,true);
            $attributes = array_merge($convertArray,$attributes);
            $unsubscribeUrl = 'https://example.com/unsubscribe?email=' . urlencode($attributes['email']);
            $attributes['unsubscribe'] = '<a href="' . $unsubscribeUrl . '">Unsubscribe</a>';
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            $value->html_content = $customerHtmlContent;
            $retargetCampaign['retarget'] = $retargetArray;            
            $retargetCampaign['is_retarget'] = 'yes';
            $this->sendTelSpielEmail($retargetCampaign,$value);
            // if($key == 0){
            //     DB::table('campaign')->whereId($campaign['campaign_id'])->update(['campaign_executed'=>true]);
            // }
        }
        exit;
    }
}
