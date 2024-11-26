<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class EmailController extends Controller
{
    public function convertToIST($timestamp) {
        $dateTime = Carbon::createFromTimestamp($timestamp);
        $dateTime->setTimezone('Asia/Kolkata');
        return $dateTime->format('Y-m-d H:i:s');
    }

    public function sendEmail(Request $request){
        // Redis::set('key', 'value');

      
        $campaign = DB::table('campaign')->select('campaign.include_segment_id',
        'campaign.rule_id','campaign.template_id','campaign.schedule',
        'templates.html_content',
        'campaign.email_subject',
        'campaign.email_from_name','campaign.id as campaign_id')->leftjoin('templates','templates.id','campaign.template_id')->orderBy('campaign_id','desc')->get()->toArray();
        $campaign = (array) current($campaign);


        $customers = DB::table('customers')->where('segment_id',$campaign['include_segment_id']);
        if(isset($campaign['rule_id']) && $campaign['rule_id'] != '') {
            $rule = DB::table('rules')->whereId($campaign['rule_id'])->first();
            if($rule){
                $ruleCondition = json_decode($rule->rule, true);
                $directColumns = ['email','name','contact_no'];
                $customers->where(function ($query) use ($ruleCondition, $rule, $directColumns) {
                    foreach ($ruleCondition as $key => $value) {
                        $conditionMethod = ($rule->rule_condition == "and") ? 'where' : 'orWhere';
                        switch ($value['options']) {
                            case 'include':
                                $searchValues = $value['values'];
                                if(str_contains($value['where'],"date")){
                                    $searchValues = $value['date_include_exclude'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod . 'In'}($value['where'], $searchValues);
                                } else {
                                    if(str_contains($value['where'],"date")){
                                        $query->{$conditionMethod . 'In'}(
                                            DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                            $searchValues
                                        );
                                    }else{
                                        $query->{$conditionMethod . 'In'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                    }

                                }
                                break;
        
                            case 'exclude':
                                $searchValues = $value['values'];
                                if(str_contains($value['where'],"date")){
                                    $searchValues = $value['date_include_exclude'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod . 'NotIn'}($value['where'], $searchValues);
                                } else {
                                    if(str_contains($value['where'],"date")){
                                        $query->{$conditionMethod . 'NotIn'}(
                                            DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                            $searchValues
                                        );
                                    }else{
                                        $query->{$conditionMethod . 'NotIn'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                    }
                                }
                                break;
        
                            case 'contains':
                                $searchValue = $value['value'];
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], 'LIKE', '%' . $searchValue . '%');
                                } else {
                                    $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'LIKE', '%' . $searchValue . '%');
                                }
                                break;
        
                            case 'not_contains':
                                $searchValue = $value['value'];
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], 'NOT LIKE', '%' . $searchValue . '%');
                                } else {
                                    if(str_contains($value['where'],"date")){
                                        $searchValue = $value['date'];
                                    }
                                    $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'NOT LIKE', '%' . $searchValue . '%');
                                }
                                break;
        
                            case 'greater_than':
                                $searchValue = $value['value'];
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], '=', $searchValue);
                                } else {
                                    $query->{$conditionMethod}(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"), 
                                        '>', 
                                        DB::raw("STR_TO_DATE('$searchValue', '%d-%m-%Y')"));
                                }
                                break;

                            case 'less_than':
                                $searchValue = $value['value'];
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], '=', $searchValue);
                                } else {
                                    $query->{$conditionMethod}(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"), 
                                        '<', 
                                        DB::raw("STR_TO_DATE('$searchValue', '%d-%m-%Y')"));
                                    // $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), '<', $searchValue);
                                }
                                break;

                            case 'range':
                                $searchValue = $value['value'];
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], '=', $searchValue);
                                } else {
                                    $date_range = $value['date_range'];
                                    // echo "<pre>";print_r($date_range);exit;
                                    $query->{$conditionMethod}(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                        '>=',
                                        DB::raw("STR_TO_DATE('" . $date_range[0] . "', '%d-%m-%Y')")
                                    )->where(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                        '<=',
                                        DB::raw("STR_TO_DATE('" . $date_range[1] . "', '%d-%m-%Y')")
                                    );
                                    // $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), '<', $searchValue);
                                }
                                break;

                        }
                    }
                });
            }
            $customers = $customers->get()->toArray();
        }else{
            $customers = $customers->get()->toArray();
        }
        // echo "<pre>";print_r($customers);exit;

        $html_content = $campaign['html_content'];
        foreach($customers as $key => $value){
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
            $this->sendSendGridEmail($campaign,$value);
            if($key == 0){
                DB::table('campaign')->whereId($campaign['campaign_id'])->update(['campaign_executed'=>true]);
            }
        }
    }

    public static function sendSendGridEmail($campaign,$customers){
        $sendGridApiKey = env('SENDGRID_EMAIL_KEY');
        $sendGridFromName = env('SENDGRID_FROM_EMAIL');

        if(isset($campaign['is_retarget']) && $campaign['is_retarget'] == 'yes'){
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => $customers->email]
                        ],
                        'custom_args' => [
                            'campaign_id' => $campaign['campaign_id'],
                            'retargeting_campaign_id' => $campaign['id']
                        ]
    
                    ]
                ],
                'from' => [
                    'email' => $sendGridFromName,
                    'name' => $campaign['retarget']['email_from_name']
                ],
                'subject' => $campaign['retarget']['email_subject'],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $customers->html_content
                    ]
                ],
                'tracking_settings' => [
                    'click_tracking' => [
                        'enable' => true,
                        'enable_text' => true
                    ],
                    'open_tracking' => [
                        'enable' => true
                    ]
                ]
            ];
        }else{
            $data = [
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => $customers->email]
                        ],
                        'custom_args' => [
                            'campaign_id' => $campaign['campaign_id']
                        ]
    
                    ]
                ],
                'from' => [
                    'email' => $sendGridFromName,
                    'name' => $campaign['email_from_name']
                ],
                'subject' => $campaign['email_subject'],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $customers->html_content
                    ]
                ],
                'tracking_settings' => [
                    'click_tracking' => [
                        'enable' => true,
                        'enable_text' => true
                    ],
                    'open_tracking' => [
                        'enable' => true
                    ]
                ]
            ];
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$sendGridApiKey.'',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        // echo "<pre>";print_r($response);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        echo "<pre>";print_r($http_status_code);
        curl_close($curl);
        return true;
    }

    public function emailWebhook(Request $request){
        Log::info('test');
        $requestData = $request->all();
        Log::info(json_encode($request->all()));
        $insertData = [];
        foreach($requestData as $key => $value){
            $insertData[$key]['campaign_id'] = $value['campaign_id'];
            $insertData[$key]['retarget_campaign_id'] = (isset($value['retargeting_campaign_id']) && $value['retargeting_campaign_id'] != '') ? $value['retargeting_campaign_id'] : '';
            $insertData[$key]['email'] = $value['email'];
            $insertData[$key]['event'] = $value['event'];
            $insertData[$key]['sg_event_id'] = $value['sg_event_id'];
        $insertData[$key]['sg_message_id'] = $value['sg_message_id'];
            $insertData[$key]['timestamp'] = $value['timestamp'];
            $insertData[$key]['indian_time'] = $this->convertToIST($value['timestamp']);
            $insertData[$key]['ip_address'] = (isset($value['ip']) && $value['ip'] != '') ? $value['ip'] : '';
            if(isset($value['useragent']) && $value['useragent'] != ''){
                $insertData[$key]['user_agent'] = $value['useragent'];
                $agent = new Agent();
                $agent->setUserAgent($value['useragent']);
                $device = $agent->device();
                $platform = $agent->platform();
                $browser = $agent->browser();
                $insertData[$key]['device'] = $device;
                $insertData[$key]['platform'] = $platform;
                $insertData[$key]['browser'] = $browser;
            }

            $insertData[$key]['user_agent'] = (isset($value['useragent']) && $value['useragent'] != '') ? $value['useragent'] : '';
            if($value['event'] == 'click'){
                $insertData[$key]['click_url'] = (isset($value['url']) && $value['url'] != '') ? $value['url'] : '';
                $ip = $value['ip'];
                $response = Http::get("http://ipinfo.io/{$ip}/json");
                if($response->successful()) {
                    $data = $response->json();
                    $insertData[$key]['city'] = $data['city'];
                    $insertData[$key]['region'] = $data['region'];
                    $insertData[$key]['country'] = $data['country'];
                    $insertData[$key]['postal'] = $data['postal'];
                }
            }
        }
        DB::table('email_analytics')->insert($insertData);
    }

    public function emailRetargetting(Request $request){
       
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
            
            $this->sendSendGridEmail($retargetCampaign,$value);
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
            $this->sendSendGridEmail($retargetCampaign,$value);
            // if($key == 0){
            //     DB::table('campaign')->whereId($campaign['campaign_id'])->update(['campaign_executed'=>true]);
            // }
        }
        exit;
    }
}
