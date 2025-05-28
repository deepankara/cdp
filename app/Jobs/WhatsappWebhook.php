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
        if(isset($whatsappData[0]) && $whatsappData[0] != ''){
            $whatsappData = json_decode($whatsappData[0], true);
        } 
        if(isset($whatsappData['entry'][0]['changes'][0]['value']['statuses'][0]) && $whatsappData['entry'][0]['changes'][0]['value']['statuses'][0] != ''){
            $statusData = $whatsappData['entry'][0]['changes'][0]['value']['statuses'][0];
            if($statusData['status'] == 'failed'){
                DB::table('whatsapp_analytics')->whereNot('status','failed')
                                                ->where('wa_id',$statusData['id'])
                                                ->delete();
                
               
            }
    
            if($statusData['status'] == 'delivered' || $statusData['status'] == 'read'){
                DB::table('whatsapp_analytics')->where('status','failed')
                                                ->where('wa_id',$statusData['id'])
                                                ->delete();
    
            }

            // if($statusData['status'] == "delivered"){
            //     DB::table('whatsapp_analytics')->where('wa_id',$statusData['id'])->where('status','sent')
            //                                     ->whereNull('time')
            //                                     ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);

            // }

            // if($statusData['status'] == "read"){
            //     DB::table('whatsapp_analytics')->where('wa_id',$statusData['id'])->where('status','sent')
            //                                     ->whereNull('time')
            //                                     ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);

                
            //     DB::table('whatsapp_analytics')->where('wa_id',$statusData['id'])->where('status','delivered')
            //                                     ->whereNull('time')
            //                                     ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);
            // }
    
            DB::table('whatsapp_analytics')->where('status',$statusData['status'])
                                            ->where('wa_id',$statusData['id'])
                                            ->update(['time'=>$this->convertToIST($statusData['timestamp'])]);

            $checkCallBack = DB::table('whatsapp_analytics')->select('mobile_number','status','time','template_id','source','api_id as ref_id')->where('wa_id',$statusData['id'])->where('status',$statusData['status'])->get()->toArray();
            $checkCallBack = (array) current($checkCallBack);
            if(isset($checkCallBack['mobile_number']) && $checkCallBack['mobile_number'] != ''){
                $callBackUrl = '';
                if($checkCallBack['source'] == 'salesforce'){
                    $callBackUrl = env('SF_CALLBACK_WA_URL_UAT');
                }
                if($callBackUrl != ''){
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $callBackUrl,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>json_encode($checkCallBack),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);
                    Log::info($response);
                    curl_close($curl);
                }
            }
        }else{
            $stopOrContinue = 'test';
            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) == 'stop'){
                $stopOrContinue = 'stop';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_unsubscribe')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['body']['text'] = "You’ve successfully unsubscribed from Auxilo updates.\n\nIf you ever wish to stay connected again, reply YES. 😊";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "yes-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "YES";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                DB::table('whatsapp_opt_in')->where('number',$insertData['number'])->delete();
            } 

            
            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) == 'stop'){
                $stopOrContinue = 'stop';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_unsubscribe')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['body']['text'] = "You’ve successfully unsubscribed from Auxilo updates.\n\nIf you ever wish to stay connected again, reply YES. 😊";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "yes-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "YES";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                DB::table('whatsapp_opt_in')->where('number',$insertData['number'])->delete();


            }

            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) == 'yes'){
                $stopOrContinue = 'continue';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_unsubscribe')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['header']['type'] = "text";
                // $whatsapp['interactive']['header']['text'] = "Greeting!!!";
                // $whatsapp['interactive']['body']['text'] = "You're now subscribed to Auxilo’s WhatsApp updates! 🎉 You’ll receive EMI due date reminders & payment confirmations directly on WhatsApp for a seamless loan experience.\n\nStay informed and never miss a payment!\n\nIf you ever wish to opt out, simply reply STOP.\n\nFor any assistance, feel free to reach out.\n\nBest,\nTeam Auxilo";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "stop-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "STOP";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                DB::table('whatsapp_unsubscribe')->where('number',$insertData['number'])->delete();


            }
            
            
            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) == 'continue'){
                $stopOrContinue = 'continue';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_opt_in')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "text";
                // $whatsapp['text']['body'] = "✅ Thank you for opting in! You’ll now receive updates, offers, and important information from Auxilo. Stay tuned! 🎉\n\nRegards,  \nTeam Auxilo";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                // Log::info($response);
                DB::table('whatsapp_unsubscribe')->where('number',$insertData['number'])->delete();


                
            } 



            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) == 'continue'){
                // Log::info('Test 3');
                $stopOrContinue = 'continue';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_opt_in')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "text";
                // $whatsapp['text']['body'] = "✅ Thank you for opting in! You’ll now receive updates, offers, and important information from Auxilo. Stay tuned! 🎉\n\nRegards,  \nTeam Auxilo";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                // Log::info($response);
                DB::table('whatsapp_unsubscribe')->where('number',$insertData['number'])->delete();


            }

            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['button']['text']) == 'yes'){
                // Log::info('Test 3');
                $stopOrContinue = 'continue';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_opt_in')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['header']['type'] = "text";
                // $whatsapp['interactive']['header']['text'] = "Greeting!!!";
                // $whatsapp['interactive']['body']['text'] = "You're now subscribed to Auxilo’s WhatsApp updates! 🎉 You’ll receive EMI due date reminders & payment confirmations directly on WhatsApp for a seamless loan experience.\n\nStay informed and never miss a payment!\n\nIf you ever wish to opt out, simply reply STOP.\n\nFor any assistance, feel free to reach out.\n\nBest,\nTeam Auxilo";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "stop-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "STOP";


                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "text";
                // // $whatsapp['text']['body'] = "✅ Thank you for opting in! You’ll now receive updates, offers, and important information from Auxilo. Stay tuned! 🎉\n\nRegards,  \nTeam Auxilo";
                // $whatsapp['text']['body'] = "";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                // Log::info($response);
                DB::table('whatsapp_unsubscribe')->where('number',$insertData['number'])->delete();


            }

            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) == 'stop'){
                $stopOrContinue = 'stop';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_unsubscribe')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['body']['text'] = "You’ve successfully unsubscribed from Auxilo updates.\n\nIf you ever wish to stay connected again, reply YES. 😊";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "yes-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "YES";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                // Log::info($response);
                DB::table('whatsapp_opt_in')->where('number',$insertData['number'])->delete();
            }

            
            if(isset($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) && strtolower($whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) == 'yes'){
                // Log::info('Test 3');
                $stopOrContinue = 'continue';
                $insertData = [];
                $insertData['number'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                $insertData['wa_id'] = $whatsappData['entry'][0]['changes'][0]['value']['messages'][0]['id'];
                $insertData['created_at'] = Carbon::now();
                DB::table('whatsapp_opt_in')->insert($insertData);

                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "text";
                // // $whatsapp['text']['body'] = "✅ Thank you for opting in! You’ll now receive updates, offers, and important information from Auxilo. Stay tuned! 🎉\n\nRegards,  \nTeam Auxilo";
                // $whatsapp['text']['body'] = "You're now subscribed to Auxilo’s WhatsApp updates! 🎉 You’ll receive EMI due date reminders & payment confirmations directly on WhatsApp for a seamless loan experience.\n\nStay informed and never miss a payment!\n\nIf you ever wish to opt out, simply reply STOP.\n\nFor any assistance, feel free to reach out.\n\nBest,\nTeam Auxilo";
                
                // $whatsapp = [];
                // $whatsapp['messaging_product'] = "whatsapp";
                // $whatsapp['to'] = $insertData['number'];
                // $whatsapp['type'] = "interactive";
                // $whatsapp['interactive']['type'] = "button";
                // $whatsapp['interactive']['header']['type'] = "text";
                // $whatsapp['interactive']['header']['text'] = "Greeting!!!";
                // $whatsapp['interactive']['body']['text'] = "You're now subscribed to Auxilo’s WhatsApp updates! 🎉 You’ll receive EMI due date reminders & payment confirmations directly on WhatsApp for a seamless loan experience.\n\nStay informed and never miss a payment!\n\nIf you ever wish to opt out, simply reply STOP.\n\nFor any assistance, feel free to reach out.\n\nBest,\nTeam Auxilo";
                // $whatsapp['interactive']['action']['buttons'][0]['type'] = "reply";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['id'] = "stop-button";
                // $whatsapp['interactive']['action']['buttons'][0]['reply']['title'] = "STOP";

                // $curl = curl_init();
                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/'.env("WHATSAPP_PHONE_ID").'/messages',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'POST',
                //     CURLOPT_POSTFIELDS =>json_encode($whatsapp),
                //     CURLOPT_HTTPHEADER => array(
                //         'Content-Type: application/json',
                //         'Authorization: Bearer '.env('WHATSAPP_API_TOKEN')
                //     ),
                // ));


                // $response = curl_exec($curl);
                // curl_close($curl);
                // $response = json_decode($response,true);
                // Log::info($response);
                DB::table('whatsapp_unsubscribe')->where('number',$insertData['number'])->delete();


            }

            

            if($stopOrContinue == 'stop' || $stopOrContinue == 'continue'){
                Log::info('TESTGIL'.$stopOrContinue);
                if(isset($insertData['number']) && $insertData['number'] != ''){
                    $number = $insertData['number'];
                }else{
                    $number = '';
                }
                Log::info('TESTGIL'.$number);

                $checkCallBacks = DB::table('whatsapp_analytics')
                ->select('mobile_number', 'status', 'time', 'template_id', 'source', 'api_id as ref_id')
                ->where('mobile_number', $number)
                ->where('status', 'read')
                ->groupBy('source')
                ->get()
                ->toArray();

                foreach($checkCallBacks as $checkCallBackObj){
                    $checkCallBack = (array) $checkCallBackObj;
                    if(isset($checkCallBack['mobile_number']) && $checkCallBack['mobile_number'] != ''){
                        $callBackUrl = '';
                        if($checkCallBack['source'] == 'salesforce'){
                            $callBackUrl = env('SF_CALLBACK_WA_URL_UAT');
                        }else{
                            continue;
                        }
                        $checkCallBack['opt_in_status'] = $stopOrContinue;
                        if($callBackUrl != ''){
                            $curl = curl_init();
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $callBackUrl,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS =>json_encode($checkCallBack),
                                CURLOPT_HTTPHEADER => array(
                                    'Content-Type: application/json'
                                ),
                            ));

                            $response = curl_exec($curl);
                            Log::info('TESTGIL');
                            Log::info($response);
                            curl_close($curl);
                        }
                    }
                }
            }

            if(isset($whatsappData['entry']['0']['changes']['0']['value']['message_template_id']) && $whatsappData['entry']['0']['changes']['0']['value']['message_template_id'] != ''){
                DB::table('whatsapp_templates')->where('name',$whatsappData['entry']['0']['changes']['0']['value']['message_template_name'])->update(['status'=>$whatsappData['entry']['0']['changes']['0']['value']['event'],'template_id'=> $whatsappData['entry']['0']['changes']['0']['value']['message_template_id']]);
            }
        }
    }

    public function convertToIST($timestamp) {
        $dateTime = Carbon::createFromTimestamp($timestamp);
        $dateTime->setTimezone('Asia/Kolkata');
        return $dateTime->format('Y-m-d H:i:s');
    }
}
