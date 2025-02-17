<?php
namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $customers;
    public $campaign;

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

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        // exit;
        $batchStartTime = Carbon::now();
        Log::info(count($this->customers));

        foreach ($this->customers as $customer) {
            try {
                $emailValidate = EmailController::validateEmail($customer->email,$$this->campaign->include_segment_id);
                $messageId = Str::random();
                if($emailValidate == 'dirty'){
                    $insertData = [
                        [
                            'campaign_id' => $campaign->id,
                            'sg_message_id' => $messageId,
                            'event' => 'processed',
                            'email' => $customer->email,
                            'indian_time' => Carbon::now(),
                        ],
                        [
                            'campaign_id' => $campaign->id,
                            'sg_message_id' => $messageId,
                            'event' => 'invalid',
                            'email' => $customer->email,
                            'indian_time' => Carbon::now(),
                        ]
                    ];

                    DB::table('email_analytics')->insert($insertData);
                    continue;
                }
                $convertArray = (array) $customer;
                $attributes = json_decode($customer->attributes,true);
                $attributes = array_merge($convertArray,$attributes);

                $customerHtmlContent = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($attributes) {
                    $key = $matches[1]; 
                    return $attributes[$key] ?? $matches[0]; 
                }, $html_content);
                $customer->html_content = $customerHtmlContent;
                $emailSend = EmailController::sendTelSpielEmail($this->campaign,$customer);
                continue;
            }catch (\Exception $e) {
                Log::error("Failed to send email to Customer ID: {$customer->id} | Error: " . $e->getMessage());
            }
        }

        $batchEndTime = Carbon::now();
        $executionTime = $batchStartTime->diffInSeconds($batchEndTime);

        Log::info("Batch processing completed | Execution Time: {$executionTime} sec");
    }
}
