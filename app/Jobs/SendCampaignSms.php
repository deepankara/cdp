<?php
namespace App\Jobs;

use App\Http\Controllers\EmailController;
use Illuminate\Support\Str;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;

class SendCampaignSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $customers;
    public $campaign;
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct($customers, $campaign)
    {
        $this->customers = $customers;
        $this->campaign = $campaign;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->campaign->queue_id)];
    }

    public function handle()
    {
        // if ($this->batch()->cancelled()) {
        //     return;
        // }
        $batchStartTime = Carbon::now();
        // Log::info(count($this->customers));
        // $smsTemplates = DB::table('sms_templates')->where("id",$this->campaign->sms_template)->get()->toArray();
        // $smsTemplates = (array) current($smsTemplates);
        $smsTemplates = $this->campaign->sms_template;
        $customer = $this->customers;
        $mainTemplate = $smsTemplates['sms'];
        $dltTemplateId = $smsTemplates['dlt_template_id'];

        $smsVariables = json_decode($this->campaign->sms_variables,true);

        $smsVar = [];
        foreach($smsVariables as $key => $value){
            $smsVar[$value['name']] = $value['value'];
        }

        // foreach ($this->customers as $customer) {
            try {
                $attributes = json_decode($customer->attributes,true);
                $attributes['name'] = $customer->name;
                $attributes['email'] = $customer->email;
                $attributes['contact_no'] = $customer->contact_no;

                $template = $mainTemplate;
                foreach ($smsVar as $placeholder => $key) {
                    $value = $whatsapp[$key] ?? $attributes[$key] ?? '';
                    $template = str_replace("{{{$placeholder}}}", $value, $template);
                }
                $phoneNumber = $attributes['contact_no'];
                $json = [
                    "username" => "AuxiloTr",
                    "dest" => substr($phoneNumber, -10),
                    "apikey" => env('TELSPICE_SMS_KEY'),
                    "signature" => "AUXILO",
                    "msgtype" => "UC",
                    "msgtxt" => $template,
                    "entityid" => env('TELSPICE_SMS_ENTITY_ID'),
                    'templateid' => $dltTemplateId,
                    'custref' => "Test"
                ];
                Log::info($json);


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

                $response = curl_exec($curl);
                $responses = json_decode($response,true);
                Log::info($response);
                foreach($responses as $response){
                    if(isset($response['code']) && $response['code'] != ''){
                        $smsAnalytics = [];
                        $smsAnalytics["campaign_id"] = $this->campaign->id;
                        $smsAnalytics['phone'] = $phoneNumber;
                        $smsAnalytics['sms'] = $template;
                        $smsAnalytics['status'] = "Processed";
                        $smsAnalytics['request_id'] = $response['reqId'];
                        $smsAnalytics['created_at'] = Carbon::now();
                        DB::table('sms_analytics')->insert($smsAnalytics);
                    }
                }
                    sleep(5);

                // continue;
            }catch (\Exception $e) {
                Log::error("Failed to send email to Customer ID: {$customer->id} | Error: " . $e->getMessage());
            }
        // }


    }
}
