<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AmazonSesEmailWebhook implements ShouldQueue
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
    // public function handle(): void
    // {
    //     $emailData = $this->data;
    //     if(isset($emailData[0]) && $emailData[0] != ''){
    //         $emailData = json_decode($emailData[0], true);
    //     }
    //     Log::info($emailData);
    //     Log::info($emailData['eventType']);
    //     if(isset($emailData['eventType']) && $emailData['eventType'] != "Send"){
    //         $tags = $emailData['mail']['tags'];
    //         Log::info($tags);
    //         if(isset($tags['CAMPAIGNID']) && $tags['CAMPAIGNID'] != ''){
    //             $insertData['campaign_id'] = current($tags['CAMPAIGNID']);
    //         }else{
    //             $insertData['template_id'] = current($tags['TEMPLATEID']);
    //         }

    //         $commonHeader = $emailData['mail']['commonHeaders'];
    //         $toHeader = current($commonHeader['to']);
    //         preg_match('/<([^>]+)>/', $toHeader, $matches);
    //         $email = $matches[1] ?? $toHeader;
    //         $insertData['email'] = $email;
    //         if($emailData['eventType'] == 'Delivery'){
    //             $eventType = 'delivered';
    //         }else{
    //             $eventType = strtolower($emailData['eventType']);
    //         }
    //         $insertData['event'] = $eventType;
    //         $insertData['sg_message_id'] = $commonHeader['messageId'];
    //         $insertData['indian_time'] = $emailData['mail']['timestamp'];
            
    //         if($eventType == "open" || $eventType == "click"){
    //             $clickOpenData = $emailData[$eventType];
    //             if(isset($clickOpenData['userAgent']) && $clickOpenData['userAgent'] != ''){
    //                 $insertData['user_agent'] = $clickOpenData['userAgent'];
    //                 $agent = new Agent();
    //                 $agent->setUserAgent($clickOpenData['userAgent']);
    //                 $device = $agent->device();
    //                 $platform = $agent->platform();
    //                 $browser = $agent->browser();
    //                 $insertData['device'] = $device;
    //                 $insertData['platform'] = $platform;
    //                 $insertData['browser'] = $browser;
    //             }

    //             if($eventType == "click"){
    //                 $insertData['click_url'] = (isset($clickOpenData['link']) && $clickOpenData['link'] != '') ? $clickOpenData['link'] : '';
    //                 if($insertData['click_url'] == env('EMAIL_UNSUB_URL')){
    //                     $unsub = [];
    //                     if(isset($tags['CAMPAIGNID']) && $tags['CAMPAIGNID'] != ''){
    //                         $unsub['campaign_id'] = current($tags['CAMPAIGNID']);
    //                     }else{
    //                         $unsub['template_id'] = current($tags['TEMPLATEID']);
    //                     }
    //                     $unsub['email_id'] = $insertData['email'];
    //                     $unsub ['created_at']= Carbon::now();
    //                     DB::table('email_unsubscribe')->insert($unsub);
    //                     return;
    //                 }
    //                 $ip = $clickOpenData['ipAddress'];
    //                 $response = Http::get("http://ipinfo.io/{$ip}/json");
    //                 if($response->successful()) {
    //                     $data = $response->json();
    //                     $insertData['city'] = $data['city'];
    //                     $insertData['region'] = $data['region'];
    //                     $insertData['country'] = $data['country'];
    //                     $insertData['postal'] = $data['postal'];
    //                 }
    //             }
    //         }
    //         DB::table('email_analytics')->insert($insertData);
    //     }
    // }

    public function handle(): void
{
    $emailData = $this->data;
    Log::info($emailData);
    if(isset($emailData[0]) && $emailData[0] != ''){
        if(!is_array($emailData)){
            $emailData = json_decode($emailData[0], true);
            $emailData = json_decode($emailData,true);
        }else{
            $emailData = $emailData[0];
            $emailData = json_decode($emailData,true);
        }
    }

    if(!is_array($emailData)){
        $emailData = json_decode($emailData,true);
    }


    if (isset($emailData['Message']) && $emailData['Message'] != '') {
        if(!is_array($emailData['Message'])){
            $emailData = json_decode($emailData['Message'], true);
        }
    }


    if (isset($emailData['eventType']) && $emailData['eventType'] !== "Send") {
        $tags = $emailData['mail']['tags'];
        $insertData = [];

        if (!empty($tags['CAMPAIGNID'])) {
            $insertData['campaign_id'] = current($tags['CAMPAIGNID']);
        } else {
            $insertData['template_id'] = current($tags['TEMPLATEID']);
        }

        $commonHeader = $emailData['mail']['commonHeaders'];
        $toHeader = current($commonHeader['to']);
        preg_match('/<([^>]+)>/', $toHeader, $matches);
        $insertData['email'] = $matches[1] ?? $toHeader;
        if($emailData['eventType'] == 'Delivery'){
            $eventType = 'delivered';
        }else{
            $eventType = strtolower($emailData['eventType']);
        }
        $insertData['event'] = $eventType;
        $insertData['sg_message_id'] = $commonHeader['messageId'];
        $insertData['indian_time'] = Carbon::parse($emailData['mail']['timestamp'])
            ->setTimezone('Asia/Kolkata')
            ->format('Y-m-d H:i:s');

        if (in_array($insertData['event'], ['open', 'click'])) {
            $clickOpenData = $emailData[$insertData['event']] ?? [];
            if (!empty($clickOpenData['userAgent'])) {
                $insertData['user_agent'] = $clickOpenData['userAgent'];
                $agent = new Agent();
                $agent->setUserAgent($clickOpenData['userAgent']);
                $insertData['device'] = $agent->device();
                $insertData['platform'] = $agent->platform();
                $insertData['browser'] = $agent->browser();
            }

            if ($insertData['event'] === 'click') {
                $insertData['click_url'] = $clickOpenData['link'] ?? '';
                if ($insertData['click_url'] === env('EMAIL_UNSUB_URL')) {
                    $unsub = [
                        'email_id' => $insertData['email'],
                        'created_at' => Carbon::now(),
                    ];
                    if (!empty($tags['CAMPAIGNID'])) {
                        $unsub['campaign_id'] = current($tags['CAMPAIGNID']);
                    } else {
                        $unsub['template_id'] = current($tags['TEMPLATEID']);
                    }
                    DB::table('email_unsubscribe')->insert($unsub);
                    return;
                }

                if (!empty($clickOpenData['ipAddress'])) {
                    try {
                        $response = Http::get("http://ipinfo.io/{$clickOpenData['ipAddress']}/json");
                        if ($response->successful()) {
                            $data = $response->json();
                            $insertData['city'] = $data['city'] ?? null;
                            $insertData['region'] = $data['region'] ?? null;
                            $insertData['country'] = $data['country'] ?? null;
                            $insertData['postal'] = $data['postal'] ?? null;
                        }
                    } catch (\Exception $e) {
                        Log::error("IP Lookup Failed: " . $e->getMessage());
                    }
                }
            }
        }
        DB::table('email_analytics')->insert($insertData);
    }else{
        Log::info($emailData);
        if(!is_array($emailData)){
            $emailData = json_decode($emailData,true);
        }
        if(isset($emailData['notificationType']) && $emailData['notificationType'] != ''){
            Log::info('TEST');
            foreach($emailData['mail']['headers'] as $header){
                if($header['name'] == "x-ses-configuration-set"){
                    return;
                }
            }
            $insertData = [
                'event' => $emailData['notificationType'] == 'Delivery' ? 'delivered' : $emailData['notificationType'],
                'email'            => $emailData['delivery']['recipients'][0] ?? null,
                'sg_message_id'        => $emailData['mail']['messageId'] ?? null,
            ];
            $insertData['indian_time'] = Carbon::parse($emailData['mail']['timestamp'])->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
            DB::table('email_analytics')->insert($insertData);
        }

    }
}


    public function convertToIST($timestamp) {
        $dateTime = Carbon::createFromTimestamp($timestamp);
        $dateTime->setTimezone('Asia/Kolkata');
        return $dateTime->format('Y-m-d H:i:s');
    }
}
