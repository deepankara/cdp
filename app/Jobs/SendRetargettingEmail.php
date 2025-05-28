<?php

namespace App\Jobs;

use App\Http\Controllers\EmailController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batchable;
use Carbon\Carbon;
use Illuminate\Queue\Middleware\WithoutOverlapping;


class SendRetargettingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $customers;
    public $campaign;
    public $retarget;
    
    public function __construct($customers, $retarget,$campaign)
    {
        $this->customers = $customers;
        $this->campaign = $campaign;
        $this->retarget = $retarget;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->campaign->queue_id)];
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $retarget = (array) $this->retarget;
        // if(is_object($this->campaign)){
        // }else{
        //     $retarget = json_decode($this->campaign,true);
        // }

        // $retarget = [];
        // foreach($retargetArray as $key => $value){
        //     if($value['when'] == "opened"){
        //         $retarget  = $value;
        //     }
        // }
        $html_content = $retarget['html_content'];
        $emailSubject = $retarget['email_subject'];
        $email_from_name = $retarget['email_from_name'];
        $customer = $this->customers;
        
        // foreach ($this->customers as $customer) {
            try {
                $attributes = json_decode($customer->attributes,true);
                $attributes['name'] = $customer->name;
                $attributes['email'] = $customer->email;
                $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                    $key = $matches[1]; 
                    return $attributes[$key] ?? $matches[0]; 
                }, $html_content);

                $attributes['html_content'] = $customerHtmlContent;
                $attributes['campaign_id'] = $this->campaign->campaign_id;
                $attributes['retarget_campaign_id'] = $this->campaign->id;
                $this->campaign->email_subject = $emailSubject;
                $this->campaign->email_from_name = $email_from_name;
                $emailSend = EmailController::sendTelSpielEmail($this->campaign,$attributes);
            }catch (\Exception $e) {
                Log::error("Failed to send email to | Error: " . $e->getMessage());
            }
        // }
    }
}
