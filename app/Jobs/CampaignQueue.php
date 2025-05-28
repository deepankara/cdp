<?php

namespace App\Jobs;

use App\Http\Controllers\RulesController;
use App\Models\Customers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use App\Jobs\SendCampaignEmail;
use Illuminate\Support\Facades\Log;

class CampaignQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $campaign;
    public $tries = 5;


    /**
     * Create a new job instance.
     */
    public function __construct($campaign)
    {
        $this->campaign = $campaign;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->campaign->id)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Start with the base query
        $customers = Customers::where('segment_id', $this->campaign->include_segment_id);

        // Apply rules if required
        if ($this->campaign->rule_id != '') {
            $customers = RulesController::rulesSync($this->campaign->rule_id, $customers);
        }

        // Apply Filtering based on Channel
        if ($this->campaign->channel == "Email") {
            $customers = $customers->leftJoin('email_unsubscribe', 'customers.email', '=', 'email_unsubscribe.email_id')
                ->whereNull('email_unsubscribe.email_id')
                ->select('customers.*'); // Ensure only necessary columns are selected
        } elseif ($this->campaign->channel == "Whatsapp") {
            $template = DB::table('whatsapp_templates')->where("id", $this->campaign->whatsapp_template)->first();

            if ($template) {
                $customers = $customers->leftJoin('whatsapp_unsubscribe', function ($join) {
                    $join->on(DB::raw('RIGHT(customers.contact_no, 10)'), '=', DB::raw('RIGHT(whatsapp_unsubscribe.number, 10)'));
                })->whereNull('whatsapp_unsubscribe.number')
                ->select('customers.*'); // Ensure only necessary columns are selected
            }   
        }

         // Fetch campaign details
        $campaign = DB::table('campaign')->whereId($this->campaign->id)->first();
        if ($campaign->channel == "Email") {
            $campaign->html_content = DB::table('templates')->whereId($campaign->template_id)->value('html_content');
            $provider = DB::table('channels')->where('source','Email')->value('provider');
        }

        if ($campaign->channel == "Whatsapp") {
            $template = DB::table('whatsapp_templates')->where("id", $campaign->whatsapp_template)->first();
            $campaign->whatsapp_template = $template ? (array) $template : null;
        }

        if ($campaign->channel == "SMS") {
            $template = DB::table('sms_templates')->where("id", $campaign->sms_template)->first();
            $campaign->sms_template = $template ? (array) $template : null;
        }

         // Process customers in chunks
        $customers->chunk(100, function ($chunk) use ($campaign) {
            foreach ($chunk as $customer) {
                $campaign->queue_id = Str::random(15);
                if($campaign->channel == "Email") {
                    dispatch(new SendCampaignEmail($customer, $campaign,$provider));
                }

                if($campaign->channel == "Whatsapp") {
                    dispatch(new SendCampaignWhatsapp($customer, $campaign));
                }

                if($campaign->channel == "SMS") {
                    dispatch(new SendCampaignSms($customer, $campaign));
                }
            }
         });

        // Process customers in chunks
        // $campaign = DB::table('campaign')
        //             ->whereId($this->campaign->id)
        //             ->first();
                    
        // $customers->chunk(1000, function ($chunk) use ($campaign) {
        //     if ($campaign->channel == "Email") {
        //         $campaign->queue_id = Str::random(5);
        //         Bus::batch([
        //             new SendCampaignEmail($chunk, $campaign)
        //         ])->dispatch();
        //         Log::info($chunk);
        //         Log::info(array($campaign));
        //     }

        //     if ($campaign->channel == "Whatsapp") {
        //         $campaign->queue_id = Str::random(5);
        //         Bus::batch([
        //             new SendCampaignWhatsapp($chunk, $campaign)
        //         ])->dispatch();
        //         Log::info($chunk);
        //         Log::info(array($campaign));
        //     }

        //     if ($campaign->channel == "SMS") {
        //         $campaign->queue_id = Str::random(5);
        //         Bus::batch([
        //             new SendCampaignSms($chunk, $campaign)
        //         ])->dispatch();
        //         Log::info($chunk);
        //         Log::info(array($campaign));
        //     }
        //     // You can add a WhatsApp job here if needed
        // });
    }
}
