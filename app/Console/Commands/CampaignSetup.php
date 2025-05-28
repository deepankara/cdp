<?php

namespace App\Console\Commands;

use App\Jobs\CampaignQueue;
use Illuminate\Console\Command;
use Log;
use DB;
use Carbon\Carbon;

class CampaignSetup extends Command
{
    protected $signature = 'campaignsetup:cron';
    protected $description = 'Process campaigns and dispatch bulk email jobs';

    public function handle()
    {
        $startTime = now()->subMinutes(5); // Check only last 5 minutes

        $campaign = DB::table('campaign')
            ->where('campaign_executed', false)
            ->where('schedule', '>=', $startTime)
            ->first();

        // If no campaign found, exit gracefully
        if (!$campaign) {
            Log::info("No campaigns found in the last 5 minutes.");
            return;
        }
        Log::info("Processing Campaign ID: " . $campaign->id);

        // Mark campaign as executed
        DB::table('campaign')
            ->where('id', $campaign->id)
            ->update(['campaign_executed' => true]);

            // Dispatch campaign job
        CampaignQueue::dispatch($campaign);
    }
}
