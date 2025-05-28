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

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $customers;
    public $campaign;
    public $provider;
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct($customers, $campaign,$provider)
    {
        $this->customers = $customers;
        $this->campaign = $campaign;
        $this->provider = $provider;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->campaign->queue_id)];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // if ($this->batch()->cancelled()) {
        //     return;
        // }
        $batchStartTime = Carbon::now();
        $html_content = $this->campaign->html_content;
        $customer = $this->customers;
        // foreach ($this->customers as $customer) {
        try {
            $emailValidate = EmailController::validateEmail($customer->email,$this->campaign->include_segment_id);
            $messageId = Str::random();
            if($emailValidate == 'dirty'){
                $insertData = [
                    [
                        'campaign_id' => $this->campaign->id,
                        'sg_message_id' => $messageId,
                        'event' => 'processed',
                        'email' => $customer->email,
                        'indian_time' => Carbon::now(),
                    ],
                    [
                        'campaign_id' => $this->campaign->id,
                        'sg_message_id' => $messageId,
                        'event' => 'invalid',
                        'email' => $customer->email,
                        'indian_time' => Carbon::now(),
                    ]
                ];

                DB::table('email_analytics')->insert($insertData);
            }
            $attributes = json_decode($customer->attributes,true);
            $attributes['name'] = $customer->name;
            $attributes['email'] = $customer->email;
            $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                $key = $matches[1]; 
                return $attributes[$key] ?? $matches[0]; 
            }, $html_content);
            $attributes['html_content'] = $customerHtmlContent;
            $attributes['campaign_id'] = $this->campaign->id;
            $this->campaign->campaign_id = $this->campaign->id;
            if($this->provider == "SES"){
                $isSendMessage = EmailController::sendAmazonSes($this->campaign,$attributes);
            }else{
                $isSendMessage = EmailController::sendTelSpielEmail($this->campaign,$attributes);
            }
        }catch (\Exception $e) {
            Log::error("Failed to send email to Customer ID: {$customer->id} | Error: " . $e->getMessage());
        }
        // }

        $batchEndTime = Carbon::now();
        $executionTime = $batchStartTime->diffInSeconds($batchEndTime);
        Log::info("Batch processing completed | Execution Time: {$executionTime} sec");
        sleep(5);
    }
}
