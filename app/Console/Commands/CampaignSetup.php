<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use DB;
use Str;
use Carbon\Carbon;
use App\Models\Customers;
use App\Jobs\SendCampaignEmail;
use Illuminate\Support\Facades\Bus;

class CampaignSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaignsetup:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process campaigns and dispatch bulk email jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $globalStartTime = Carbon::now(); // Start time of the entire process
        $this->info("Campaign process started at: " . $globalStartTime);
        Log::info("Campaign process started at: " . $globalStartTime);

        $startTime = Carbon::now()->subMinutes(10);
        $endTime = Carbon::now()->addMinutes(30);

        $campaign = DB::table('campaign')
            ->where('campaign_executed', false)
            // ->whereBetween('schedule', [$startTime, $endTime])
            ->first();

        // if (!$campaign) {
        //     $this->info("No campaigns found in the given time range.");
        //     Log::info("No campaigns found in the given time range.");
        //     return;
        // }

        // $this->info("Processing campaign ID: {$campaign->id}");
        // Log::info("Processing campaign ID: {$campaign->id}");

        // Dispatching jobs in bulk
        Customers::where('segment_id', 4)
            ->chunk(1000, function ($customers) use ($campaign) {
                $batchStartTime = Carbon::now();
                $campaign->queue_id = Str::random();    
                if($campaign->channel == "Email"){
                    Bus::batch([
                        new SendCampaignEmail($customers, $campaign)
                    ])->dispatch();
                }

                $batchEndTime = Carbon::now();
                $batchExecutionTime = $batchStartTime->diffInSeconds($batchEndTime);

                $this->info("Dispatched batch of " . count($customers) . " customers | Execution Time: {$batchExecutionTime} sec");
                Log::info("Dispatched batch of " . count($customers) . " customers | Execution Time: {$batchExecutionTime} sec");
            });

        $globalEndTime = Carbon::now();
        $totalExecutionTime = $globalStartTime->diffInSeconds($globalEndTime);
        
        $this->info("Campaign process completed at: " . $globalEndTime);
        Log::info("Campaign process completed at: " . $globalEndTime);
        $this->info("Total Execution Time: {$totalExecutionTime} sec");
        Log::info("Total Execution Time: {$totalExecutionTime} sec");
    }
}
