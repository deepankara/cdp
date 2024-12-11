<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
    $users = DB::table('customers')->where('email','gilchristdsouza105@gmail.com')->get()->toArray();
    $users = (array) current($users);

    $emailAnalytics = DB::table('email_analytics_api')
                        ->select('event', DB::raw('COUNT(DISTINCT sg_message_id) as count'))
                        ->where('email', 'gilchristdsouza105@gmail.com')
                        ->groupBy('event')
                        ->get()->toArray();

    


    $mobileNumber = $users['contact_no'];
    $normalizedPhoneNumber = preg_replace('/\D/', '', $mobileNumber);

    $whatsapp = DB::table('whatsapp_analytics')->where('mobile_number',$normalizedPhoneNumber)
                                                ->select('status', DB::raw('count(*) as status_count'))
                                                ->groupBy('status')->get()->toArray();





$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://enterprise.smsgupshup.com/GatewayAPI/rest',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'userid=2000175766&password=uAIyxtLya&send_to=7977251637&method=SendMessage&msg=Dear%20Gilchrist%2C%20your%20EMI%20payment%20of%20Rs.123%2F-%20of%20loan%20account%20number%20123%20123%20is%20due%20on%20123.%20Please%20ensure%20enough%20funds%20towards%20clearance.%20Kindly%20ignore%20if%20paid.%20Regards%2C%20Team%20Auxilo&msg_type=TEXT&auth_scheme=plain&v=1.1&format=json',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


    $whatsappTemplate = DB::table('whatsapp_templates')->where('id',1)->get()->toArray();
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
        $header['text'] = $whatsappTemplate['header_name'];
        if(str_contains($whatsappTemplate['header_name'],"{{1}}")){
            $header['example']['header_text'] = json_decode($whatsappTemplate['header_variables_sample'],true)["{{1}}"];
        }
        array_push($json['components'],$header);
    }

    $body = [];
    $body['type'] = 'BODY';
    $body['text'] = $whatsappTemplate['html_content'];
    if(str_contains($whatsappTemplate['html_content'],"{{1}}")){
        $body['example']['body_text'] = array_values(json_decode($whatsappTemplate['header_variables_sample'],true));
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

                if($value['option'] == "COPY_OFFER_CODE"){
                    $buttonForJson['offer_code'] = $value['offer_code'];
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
            'Authorization: Bearer EAAWGi4ndejABO8EtVH7VshON4woV0vJAB1YrZBF08YNZA4b6ZCQZA7LwRcMmeMww9XWKKCGCfMzZB6KcBbU5YCOLx6I8IczFQz93hj0Mxb6bC3RNL1PuaKrNB7r9yAxJmxBZBBXXiCE8uHwuMGa5cURu3O2oLZCMEMm6WnSqZC50IDN5ZBt8bnLP3fEUmjqqqRdoZB271vKp99ZBudUdJQv8SeWPrZB98CmCvnY5qKZBTdM1zxLoZD',
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);


    echo "<pre>";print_r($response);
    echo "<pre>";print_r($json);exit;






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

