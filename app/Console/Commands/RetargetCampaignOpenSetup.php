<?php

namespace App\Console\Commands;

use App\Jobs\CampaignQueue;
use App\Jobs\RetargetCampaignOpenQueue;
use Illuminate\Console\Command;
use Log;
use DB;
use Carbon\Carbon;

class RetargetCampaignOpenSetup extends Command
{
    protected $signature = 'retargetcampaignopensetup:cron';
    protected $description = 'Process campaigns and dispatch bulk email jobs';

    public function handle()
    {
        $fiveMinutesAgo = Carbon::now()->subMinutes(20)->format('Y-m-d H:i');
        $now = Carbon::now()->format('Y-m-d H:i');
        
        $campaign = DB::table('retarget_campaign')
                    ->whereRaw("
                        JSON_SEARCH(retarget_campaign.retarget, 'one', '%opened%', NULL, '$[*].when') IS NOT NULL
                        AND EXISTS (
                            SELECT 1 FROM JSON_TABLE(
                                retarget_campaign.retarget, 
                                '$[*]' COLUMNS (
                                    schedule VARCHAR(16) PATH '$.schedule'
                                )
                            ) AS jt
                            WHERE LEFT(jt.schedule, 16) BETWEEN ? AND ?
                        )
                    ", [$fiveMinutesAgo, $now])
                    ->where('is_open_executed', false)
                    ->orderBy('retarget_campaign.id', 'desc')
                    ->first();

        if(!$campaign) {
            Log::info("No Retarget campaigns found in the last 5 minutes.");
            Log::info($now);
            Log::info($fiveMinutesAgo);
            return;
        }

        Log::info("Processing Retarget Campaign ID: " . $campaign->id);
        DB::table('retarget_campaign')
            ->where('id', $campaign->id)
            ->update(['is_open_executed' => true]);

        RetargetCampaignOpenQueue::dispatch($campaign);
        Log::info("Campaign Query Result: ", (array) $campaign);
    }
}
