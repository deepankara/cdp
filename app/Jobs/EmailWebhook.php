<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $requestData = $this->data;
        if(isset($requestData[0]) && $requestData[0] != ''){
            $requestData = json_decode($requestData[0], true);
        }
        Log::info($requestData);
        if(isset($requestData['custom_fields']['user_custom_args']['TYPE']) && $requestData['custom_fields']['user_custom_args']['TYPE'] == 'CAMPAIGN'){
            $insertData['campaign_id'] = $requestData['custom_fields']['user_custom_args']['CAMPAIGNID'];
            if(isset($requestData['custom_fields']['user_custom_args']['retarget_campaign_id']) && $requestData['custom_fields']['user_custom_args']['retarget_campaign_id'] != ''){
                $insertData['retarget_campaign_id'] = $requestData['custom_fields']['user_custom_args']['retarget_campaign_id'];
            }
        }else{
            $insertData['template_id'] = $requestData['custom_fields']['user_custom_args']['TEMPLATEID'];
        }
        $insertData['email'] = $requestData['email_id'];
        $insertData['event'] = $requestData['event'];
        $insertData['sg_message_id'] = $requestData['custom_fields']['guid'];
        $insertData['indian_time'] = $requestData['timestamp'];
        $insertData['ip_address'] = (isset($requestData['ip']) && $requestData['ip'] != '') ? $requestData['ip'] : '';
        
        if(isset($requestData['useragent']) && $requestData['useragent'] != ''){
            $insertData['user_agent'] = $requestData['useragent'];
            $agent = new Agent();
            $agent->setUserAgent($requestData['useragent']);
            $device = (isset($requestData['device']) && $requestData['device'] != '') ? $requestData['device'] : '';
            $platform = $agent->platform();
            $browser = $agent->browser();
            $insertData['device'] = $device;
            $insertData['platform'] = $platform;
            $insertData['browser'] = $browser;
        }

        if($requestData['event'] == 'click'){
            $insertData['click_url'] = (isset($requestData['url']) && $requestData['url'] != '') ? $requestData['url'] : '';
            $ip = $requestData['ip'];
            $response = Http::get("http://ipinfo.io/{$ip}/json");
            if($response->successful()) {
                $data = $response->json();
                $insertData['city'] = $data['city'];
                $insertData['region'] = $data['region'];
                $insertData['country'] = $data['country'];
                $insertData['postal'] = $data['postal'];
            }
        }

        if($requestData['event'] == 'unsubscribe'){
            $unsub = [];
            if(isset($requestData['custom_fields']['user_custom_args']['TYPE']) && $requestData['custom_fields']['user_custom_args']['TYPE'] == 'CAMPAIGN'){
                $unsub['campaign_id'] = $requestData['custom_fields']['user_custom_args']['CAMPAIGNID'];
            }else{
                $unsub['template_id'] = $requestData['custom_fields']['user_custom_args']['TEMPLATEID'];
            }
            $unsub['email_id'] = $insertData['email'];
            $unsub ['created_at']= Carbon::now();
            DB::table('email_unsubscribe')->insert($unsub);
        }
        DB::table('email_analytics')->insert($insertData);
    }
}