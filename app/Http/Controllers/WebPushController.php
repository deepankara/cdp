<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use Carbon\Carbon;
use DB;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushController extends BaseController
{
    public function createPushNotifcation(Request $request){
        $requestData = $request->all();
        if(isset($requestData['endpoint']) && $requestData['endpoint'] != ''){
            $requestData;
            $insertData['endpoint'] = $requestData['endpoint'];
            // $insertData['expirationTime'] = $requestData['expirationTime'] == '' : null ? $requestData['expirationTime'];
            $insertData['p256dh'] = $requestData['keys']['p256dh'];
            $insertData['auth'] = $requestData['keys']['auth'];
            $insertData['created_at'] = Carbon::now();

            $subscription = Subscription::create([
                'endpoint' => $requestData['endpoint'],
                'publicKey' => $requestData['keys']['p256dh'],
                'authToken' => $requestData['keys']['auth'],
            ]);

            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => 'Welcome',
                    'publicKey' => env('VAPID_PUBLIC_KEY'),
                    'privateKey' => env('VAPID_PRIVATE_KEY'),
                ]
            ]);

            $payload = json_encode([
                'title' => 'Welcome to Auxilo Finserve, Gilchrist 🚀',
                'body' => 'Gilchrist, Please check Loan Options',
                'icon'=> "https://www.auxilo.com/assets/images/favicon.png",
                'image'=> "https://img-cdn.thepublive.com/fit-in/1200x675/entrackr/media/post_attachments/wp-content/uploads/2024/02/Auxilo.jpg",
                'url' => 'https://www.auxilo.com/'
            ]);
              
            $report = $webPush->sendOneNotification($subscription, $payload);
            return response()->json(['Successfully'], 200);
        }else{
            return response()->json(['Bad Request'], 401);
        }
        
        echo "<pre>";print_r($insertData);exit;
    }
}