<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SmsAnalytics;

class SmsWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $smsRequest = $this->data;
        Log::info($smsRequest);
        if(isset($smsRequest[0]) && $smsRequest[0] != ''){
            $smsRequest = json_decode($smsRequest[0], true);
            if(isset($smsRequest['status']) && $smsRequest['status'] != ''){
                $smsData = SmsAnalytics::where('request_id',$smsRequest['sid'])->first()->replicate();
                $smsData->status = $smsRequest['reason'];
                $smsData->created_at = isset($smsRequest['dtime']) ? $smsRequest['dtime'] : Carbon::now();
                $smsData->save();
            }
        } 
    }
}
