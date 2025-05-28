<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Queue\Middleware\WithoutOverlapping;


class ReportingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Batchable;

    public $campaign;


    /**
     * Create a new job instance.
     */
    public function __construct($campaign)
    {
        $this->campaign = $campaign;

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
        $segment = DB::table('segment')->whereId($campaign->include_segment_id)->get()->toArray();
        $segment = current($segment);

        if($campaign->channel == "Email") {
            
            $email = DB::table('email_analytics')
            ->select('event', DB::raw('COUNT(DISTINCT email) as user_count'))
            ->where('campaign_id', $campaign->id)
            ->groupBy('event')
            ->pluck('user_count', 'event')->toArray();

            $emailDump = EmailDump::select('status', DB::raw('COUNT(DISTINCT email) as user_count'))
                        ->where('segment_id',$campaign->include_segment_id)
                        ->groupBy('status')
                        ->pluck('user_count','status')->toArray();



            $data = [
                'campaign_name' => $campaign->name,
                'campaign_date' => Carbon::parse('schedule')->format('D-M-Y'),
                'email' => $segment->email,
                'name' => $segment->name,
                'result'=> $email,
                'emailDump' => $emailDump
            ];

            Mail::send('reportingEmail', $data, function ($message) {
                $message->to($data['email'], $data['name'])
                        ->subject('📩 Email Campaign Performance Report – '.$data['campaign_name']);
            });

            
        }

        if($campaign->channel == "Whatsapp") {

            $whatsapp = DB::table('whatsapp_analytics')
                ->select('status', DB::raw('count(*) as status_count'))
                ->whereNotNull('time')->where('campaign_id',$campaign->id)
                ->groupBy('status')
                ->get()->toArray();
            
            
        }

        if($campaign->channel == "SMS") {
            dispatch(new SendCampaignSms($customer, $campaign));
        }
    }
}
