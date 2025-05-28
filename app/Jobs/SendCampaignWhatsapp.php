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

class SendCampaignWhatsapp implements ShouldQueue
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
        // $template = DB::table('whatsapp_templates')->where("id",$this->campaign->whatsapp_template)->get()->toArray();
        // $template = (array) current($template);
        $template  = $this->campaign->whatsapp_template;
        $customer = $this->customers;
        $whatsappVariables = json_decode($this->campaign->wa_variables,true);

        

        // foreach($this->customers as $customer){
            try {
                $component = [];
                $attributes = json_decode($customer->attributes,true);
                $attributes['name'] = $customer->name;
                $attributes['email'] = $customer->email;
                $attributes['contact_no'] = $customer->contact_no;
                // if($customer->contact_no == "6361136303"){
                //     continue;
                // }

                if (isset($template['header_type']) && $template['header_type'] != 'NONE') {
                    if(str_contains($template['header_name'],"{{1}}")){

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
            // Log::info($whatsapp);


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
            Log::info(json_encode($response));
            if(isset($response['messages'][0]['id']) && $response['messages'][0]['id'] != '') {
                $statuses = ["read", "sent", "delivered", "failed"];
                $whatsappData = [];
                foreach ($statuses as $status) {
                    $whatsappData[] = [
                        'wa_id' => $response['messages'][0]['id'],
                        'status' => $status,
                        'mobile_number' => str_replace('+','',$attributes['contact_no']),
                        'created_at' => Carbon::now(),
                        'campaign_id' => $this->campaign->id,
                    ];
                }
                DB::table("whatsapp_analytics")->insert($whatsappData);
            }
            }catch (\Exception $e) {
                Log::error("Failed to send email to Customer ID: {$customer->id} | Error: " . $e->getMessage());
            }
        // }

        $batchEndTime = Carbon::now();
        $executionTime = $batchStartTime->diffInSeconds($batchEndTime);
        Log::info("Batch processing completed | Execution Time: {$executionTime} sec");
        sleep(6);
    }
}
