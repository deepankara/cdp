<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EmailController;
use Jenssegers\Agent\Agent;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    $users = DB::table('customers')->where('segment_id',1)->get()->toArray();
    foreach($users as $key => $value){
        $json = json_decode($value->attributes,true);
        $json['loan_amount'] = "45,000";
        $json["loan_account_no"] = "10235677";
        $json["loan_date"] = "06/02/2024";
        DB::table('customers')->whereId($value->id)->update(["attributes"=>json_encode($json)]);
    }
    exit;

    

    $whatsapp = DB::table('whatsapp_templates')->whereId(4)->get()->toArray();
    $whatsapp = (array) current($whatsapp);
    $buttons = json_decode($whatsapp['buttons'],true);

    $jsonButton = [];

    

    if(count($buttons) >= 1){
        foreach($buttons as $Key => $value){
            if($value['option'] == "URL"){
                $button = [];
                $button['type'] = "button";
                $button['sub_type'] = strtolower($value['option']);
                $button['index'] = $key;
                $button['parameters']['type'] = "coupon_code";
                $button['parameters']['coupon_code'] = $requestData['template_attr']['button']['button_offer_code'];
                $button['parameters'] = array($button['parameters']);
            }
        }
    }

    echo "<pre>";print_r($buttons);exit;

    $email = DB::table('email_analytics')->distinct('sg_message_id')->count();
    $whatsapp = DB::table('whatsapp_analytics')->distinct('wa_id')->count();
    $sms = DB::table('sms_analytics')->count();

//     $whatsappTemplate = DB::table('whatsapp_templates')->where('id',2)->get()->toArray();
//     $whatsappTemplate = (array) current($whatsappTemplate);

    



//     $users = DB::table('customers')->where('email','gilchristdsouza105@gmail.com')->get()->toArray();
//     $users = (array) current($users);

//     $emailAnalytics = DB::table('email_analytics_api')
//                         ->select('event', DB::raw('COUNT(DISTINCT sg_message_id) as count'))
//                         ->where('email', 'gilchristdsouza105@gmail.com')
//                         ->groupBy('event')
//                         ->get()->toArray();

    


//     $mobileNumber = $users['contact_no'];
//     $normalizedPhoneNumber = preg_replace('/\D/', '', $mobileNumber);

//     $whatsapp = DB::table('whatsapp_analytics')->where('mobile_number',$normalizedPhoneNumber)
//                                                 ->select('status', DB::raw('count(*) as status_count'))
//                                                 ->groupBy('status')->get()->toArray();





// $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://enterprise.smsgupshup.com/GatewayAPI/rest',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'POST',
//   CURLOPT_POSTFIELDS => 'userid=2000175766&password=uAIyxtLya&send_to=7977251637&method=SendMessage&msg=Dear%20Gilchrist%2C%20your%20EMI%20payment%20of%20Rs.123%2F-%20of%20loan%20account%20number%20123%20123%20is%20due%20on%20123.%20Please%20ensure%20enough%20funds%20towards%20clearance.%20Kindly%20ignore%20if%20paid.%20Regards%2C%20Team%20Auxilo&msg_type=TEXT&auth_scheme=plain&v=1.1&format=json',
//   CURLOPT_HTTPHEADER => array(
//     'Content-Type: application/x-www-form-urlencoded'
//   ),
// ));

// $response = curl_exec($curl);

// curl_close($curl);
// echo $response;


    $whatsappTemplate = DB::table('whatsapp_templates')->where('id',3)->get()->toArray();
    $whatsappTemplate = (array) current($whatsappTemplate);


    $json = [];
    $json['name'] = $whatsappTemplate['name'];
    $json['language'] = $whatsappTemplate['language'];
    $json['category'] = $whatsappTemplate['category'];
    $json['components'] = [];

    if(isset($whatsappTemplate['header_type']) && $whatsappTemplate['header_type'] != 'NONE'){
        $header = [];
        $header['type'] = "HEADER";
        $header['format'] = $whatsappTemplate['header_type'];
        if($whatsappTemplate['header_type'] == "TEXT"){
            $header['text'] = $whatsappTemplate['header_name'];
            if(str_contains($whatsappTemplate['header_name'],"{{1}}")){
                $header['example']['header_text'] = json_decode($whatsappTemplate['header_variables_sample'],true)["{{1}}"];
            }
        }

        if($whatsappTemplate['header_type'] == "IMAGE" || $whatsappTemplate['header_type'] == "DOCUMENT"){
            $header['example']['header_handle'] = array($whatsappTemplate['media_id']);
        }
        array_push($json['components'],$header);
    }

    $body = [];
    $body['type'] = 'BODY';
    $body['text'] = $whatsappTemplate['html_content'];
    if(str_contains($whatsappTemplate['html_content'],"{{1}}")){
        $body['example']['body_text'] = array_values(json_decode($whatsappTemplate['body_variables_sample'],true));
    }
    array_push($json['components'],$body);

    if(isset($whatsappTemplate['content']) && $whatsappTemplate['content'] != ''){
        $footer = [];
        $footer['type'] = 'FOOTER';
        $footer['text'] = $whatsappTemplate['content'];
        array_push($json['components'],$footer);
    }

    if(isset($whatsappTemplate['buttons']) && $whatsappTemplate['buttons'] != ''){
        $buttonsJson = json_decode($whatsappTemplate['buttons'],true);
        if(count($buttonsJson) >= 1){
            $buttons = [];
            $buttons['type'] = 'BUTTONS';
            $buttons['buttons'] = [];
            foreach($buttonsJson as $key => $value){
                $buttonForJson = [];
                $buttonForJson['type'] = $value['option'];
                $buttonForJson['text'] = $value['button_text'];
                if($value['option'] == 'URL'){
                    $buttonForJson['url'] = $value['url'];
                }

                if($value['option'] == "PHONE_NUMBER"){
                    $buttonForJson['phone_number'] = $value['phone_number'];
                }

                if($value['option'] == "COPY_CODE"){
                    unset($buttonForJson['text']);
                    $buttonForJson['example'] = $value['offer_code'];
                }
                array_push($buttons['buttons'],$buttonForJson);
            }
            array_push($json['components'],$buttons);
        }
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://graph.facebook.com/v21.0/105692085530607/message_templates',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($json),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.env("WHATSAPP_API_TOKEN"),
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);


    echo "<pre>";print_r($json);
    echo "<pre>";print_r(json_decode($response,true));exit;






    echo "<pre>";print_r($whatsappTemplate);exit;
    $url = 'https://www.auxilo.com/emailers/ace-pack/'; 
    $httpClient = HttpClient::create();
    $response = $httpClient->request('GET', $url);
    $htmlContent = $response->getContent();
    $crawler = new Crawler($htmlContent);
    $html = $crawler->html();
    return $html;
});

Route::get('/sendEmail', [EmailController::class, 'sendEmail']);
Route::get('/sendRetargetting', [EmailController::class, 'emailRetargetting']);
Route::get('/sendClickRetargetting', [EmailController::class, 'emailRetargettingOpen']);
Route::get('/sendWhatsapp', [EmailController::class, 'sendWa']);
Route::get('/sendSms', [EmailController::class, 'sendCampSms']);

