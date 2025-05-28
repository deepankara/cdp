<?php

namespace App\Jobs;

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

class RetargetCampaignClickQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $retarget_campaign;

    /**
     * Create a new job instance.
     */
    public function __construct($campaign)
    {
        $this->retarget_campaign = $campaign;
    }

    public function middleware(): array
    {
        return [new WithoutOverlapping($this->retarget_campaign->id)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaignId = $this->retarget_campaign->campaign_id;

        $campagin = DB::table('campaign')->whereId($campaignId)->get()->toArray();
        $campagin = (array) current($campagin);
        $segmentId = $campagin['include_segment_id'];
        
        $retargetArray = json_decode($this->retarget_campaign->retarget,true);
        $retarget = [];
        foreach($retargetArray as $key => $value){
            if($value['when'] == "clicked"){
                $retarget  = $value;
            }
        }
        
        $campaignData = DB::table('retarget_campaign')
        ->whereId($this->retarget_campaign->id)
        ->first();
        // $segmentId = $campaignData->include_segment_id;

        $retarget = (object) $retarget;
        DB::table('email_analytics as ea')
        ->leftJoin('customers as c', 'ea.email', 'c.email')
        ->where('ea.campaign_id', $campaignId)
        ->where('c.segment_id', $segmentId)
        ->where('ea.event', 'delivered')
        ->whereNotExists(function ($query) use ($campaignId,$retarget) {
            $query->select(DB::raw(1))
                ->from('email_analytics as sub')
                ->where('sub.campaign_id', $campaignId)
                ->where('sub.event', 'click')
                ->where('sub.click_url',$retarget->click_link)
                ->whereRaw('sub.email = ea.email');
        })
        ->select('ea.email', 'ea.campaign_id', 'ea.event', 'c.attributes', 'c.segment_id','c.name')
        ->orderBy('ea.email') // Add orderBy before groupBy
        ->groupBy('ea.email')
        ->chunk(100, function ($customers) use ($retarget,$campaignData) {
            
            foreach ($customers as $customer) {
                $campaignData->queue_id = Str::random(15);
                dispatch(new SendRetargettingEmail($customer, $retarget,$campaignData));
            }
            // Bus::batch([
            //     new SendRetargettingEmail($customers, $retarget)
            // ])->dispatch();
        });
    
    }
}
