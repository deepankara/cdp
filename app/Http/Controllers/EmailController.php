<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class EmailController extends Controller
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
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod . 'In'}($value['where'], $searchValues);
                                } else {
                                    $query->{$conditionMethod . 'In'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                }
                                break;
        
                            case 'exclude':
                                $searchValues = $value['values'];
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod . 'NotIn'}($value['where'], $searchValues);
                                } else {
                                    $query->{$conditionMethod . 'NotIn'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                }
                                break;
        
                            case 'contains':
                                $searchValue = $value['value'];
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], 'LIKE', '%' . $searchValue . '%');
                                } else {
                                    $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'LIKE', '%' . $searchValue . '%');
                                }
                                break;
        
                            case 'not_contains':
                                $searchValue = $value['value'];
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], 'NOT LIKE', '%' . $searchValue . '%');
                                } else {
                                    $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'NOT LIKE', '%' . $searchValue . '%');
                                }
                                break;
        
                            case 'equals_to':
                                $searchValue = $value['value'];
                                if (in_array($value['where'], $directColumns)) {
                                    $query->{$conditionMethod}($value['where'], '=', $searchValue);
                                } else {
                                    $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), '=', $searchValue);
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
            $insertData[$key]['email'] = $value['email'];
            $insertData[$key]['event'] = $value['event'];
            $insertData[$key]['sg_event_id'] = $value['sg_event_id'];
            $insertData[$key]['sg_message_id'] = $value['sg_message_id'];
            $insertData[$key]['timestamp'] = $value['timestamp'];
            $insertData[$key]['indian_time'] = $this->convertToIST($value['timestamp']);
            $insertData[$key]['ip_address'] = (isset($value['ip']) && $value['ip'] != '') ? $value['ip'] : '';
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

    
}
