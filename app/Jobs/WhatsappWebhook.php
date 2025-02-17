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

class WhatsappWebhook implements ShouldQueue
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
        $whatsappData = $this->data;
        if(isset($whatsappData['entry'][0]['changes'][0]['value']['statuses'][0]) && $whatsappData['entry'][0]['changes'][0]['value']['statuses'][0] != ''){
            $statusData = $whatsappData['entry'][0]['changes'][0]['value']['statuses'][0];
            Log::info($statusData);
            
            if($statusData['status'] == 'failed'){
                DB::table('whatsapp_analytics')->whereNot('status','failed')
                                                ->where('wa_id',$statusData['id'])
                                                ->delete();
                
               
            }
    
            if($statusData['status'] == 'delivered'){
                DB::table('whatsapp_analytics')->where('status','failed')
                                                ->where('wa_id',$statusData['id'])
                                                ->delete();
    
            }
    
            DB::table('whatsapp_analytics')->where('status',$statusData['status'])
                                            ->where('wa_id',$statusData['id'])
                                            ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);
            
        }else{
            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) == 'stop'){
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_unsubscribe')->insert($insertData);
            } 
        }
    }

    public function convertToIST($timestamp) {
        $dateTime = Carbon::createFromTimestamp($timestamp);
        $dateTime->setTimezone('Asia/Kolkata');
        return $dateTime->format('Y-m-d H:i:s');
    }
}
