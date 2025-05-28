<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\Session;
// use App\Http\Controllers\EmailController;
// use App\Http\Controllers\WhatsappController;
// use App\Http\Controllers\SmsController;
// use Jenssegers\Agent\Agent;
// use Carbon\Carbon;
// use Symfony\Component\BrowserKit\HttpBrowser;
// use Symfony\Component\HttpClient\HttpClient;
// use Symfony\Component\DomCrawler\Crawler;

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

// Route::get('/check-whatsapp', function () {
//     $test = DB::table('whatsapp_analytics as sent')
//     ->join('whatsapp_analytics as delivered', function ($join) {
//         $join->on('sent.wa_id', '=', 'delivered.wa_id')
//              ->where('sent.status', 'sent')
//              ->whereNull('sent.time') // Fetch only sent records with null time
//              ->where('delivered.status', 'delivered');
//     })
//     ->where('delivered.campaign_id', 24)
//     ->select('sent.*') // Fetch only sent records
//     ->get()
//     ->toArray();

//     echo "<pre>";print_r($test);exit;
//     // ->update([
//     //     'sent.time' => DB::raw('delivered.time') // Update sent record time from delivered record
//     // ]);

// });

Route::get('/', function () {
    // $db = DB::table('customers')->where('segment_id',39)->get()->toArray();
    // foreach($db as $key => $value){
    //     $imageLink = 'https://www.auxilo.com/assets/images/Akbar-Travels.jpg';
    //     $jsonAttributes = json_decode($db[$key]->attributes,true);
    //     $jsonAttributes['image_link'] = $imageLink;
    //     DB::table('customers')->where('segment_id',39)->whereId($value->id)->update(['attributes'=>json_encode($jsonAttributes)]);
    // }
//      $test = Mail::html("<table style='border: 1px solid #cccccc; font-family: Arial, Helvetica, sans-serif;' border='0' width='650' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td width='650' height='548'><img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/img1.png' alt='Auxilo' width='650' height='548' /></td> </tr> <tr> <td> <table border='0' width='650' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td width='50'>&nbsp;</td> <td align='center' width='550'> <h1 style='font-size: 20px; color: #f37022; margin-bottom: 5px;'>Dear Gilchrist,</h1> <p style='font-size: 16px; color: #231f20; font-weight: normal; line-height: 25px; margin-top: 0;'>We&rsquo;re thrilled to let you know that the launch of the <br /><span style='color: #1b80ba; font-weight: 600;'>Impact X Scholarship Program</span> is just around the corner!<br />Thank you for applying and for your enthusiastic participation in this journey. With the application process set to begin soon, we want to ensure you&rsquo;re fully prepared. <br /><br />To make the process smoother, here&rsquo;s a checklist of documents you&rsquo;ll need to keep handy:</p> </td> <td width='50'>&nbsp;</td> </tr> </tbody> </table> </td> </tr> <tr> <td width='650' height='751'><img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/img2.png' alt='Auxilo' width='650' height='751' /></td> </tr> <tr> <td> <table border='0' width='650' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td width='50'>&nbsp;</td> <td align='center' width='550'> <p style='font-size: 16px; color: #231f20; font-weight: normal; line-height: 25px;'>The <span style='color: #1b80ba; font-weight: 600;'>Impact X Scholarship Program </span>is the flagship initiative under <span style='color: #1b80ba; font-weight: 600;'>Edevate</span>, Auxilo&rsquo;s dedicated CSR platform for driving social impact. Through this program, we aim to empower individuals like you to achieve your dreams and create a lasting difference. <br />We&rsquo;re excited to begin this journey with you. Keep an eye on your inbox for the official announcement, it&rsquo;s coming very soon!</p> <p style='font-size: 16px; color: #231f20; font-weight: normal; line-height: 25px;'>Here&rsquo;s to your bright future, <br /><span style='color: #f37022; font-weight: 600;'> Team Auxilo Finserve </span></p> </td> <td width='50'>&nbsp;</td> </tr> </tbody> </table> </td> </tr> <tr style='background-color: #ffeade;'> <td> <table border='0' width='650' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td>&nbsp;</td> </tr> <tr> <td> <table border='0' width='650' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td align='center' width='200'><a href='https://www.youtube.com/channel/UClc_BEIN262Fm5eY_8sPA5w?view_as=subscriber'> <img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/yt.png' alt='' width='20' height='20' /> </a> <p style='font-size: 14px; color: #080808; margin-top: 15px; margin-bottom: 0; font-weight: 600;'>Auxilo Education Loan</p> </td> <td width='40'> <div style='background-color: #cccccc; width: 1px; height: 50px; display: block; margin: 0 auto;'>&nbsp;</div> </td> <td align='center' width='200'><a href='https://www.linkedin.com/company/auxilo/'> <img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/in.png' alt='' width='20' height='20' /> </a> <p style='font-size: 14px; color: #080808; margin-top: 15px; margin-bottom: 0; font-weight: 600;'>Auxilo Finserve PVT LTD</p> </td> <td width='40'> <div style='background-color: #cccccc; width: 1px; height: 50px; display: block; margin: 0 auto;'>&nbsp;</div> </td> <td align='center' width='170'> <table border='0' width='170' cellspacing='0' cellpadding='0' align='center'> <tbody> <tr> <td width='60'>&nbsp;</td> <td align='center' width='30'><a href='https://www.instagram.com/auxilofinserve/'> <img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/insta.png' alt='' width='20' height='20' /> </a></td> <td align='center' width='30'><a href='https://www.facebook.com/AuxiloFinserve/'> <img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/fb.png' alt='' width='20' height='20' /> </a></td> <td align='center' width='30'><a href='https://x.com/i/flow/login?redirect_after_login=%2FAuxiloFinserve'> <img style='display: block; border: 0;' src='https://www.auxilo.com/emailers/impactx-scholarship/tw.png' alt='' width='20' height='20' /> </a></td> <td width='60'>&nbsp;</td> </tr> </tbody> </table> <p style='font-size: 14px; color: #080808; margin-top: 15px; margin-bottom: 0; font-weight: 600;'>Auxilo Finserve</p> </td> </tr> </tbody> </table> </td> </tr> <tr> <td>&nbsp;</td> </tr> </tbody> </table> </td> </tr> </tbody> </table>", function ($message) {
//                 $message->to('gilchrist.auxilo@gmail.com')
//                         ->subject('The Title');
//                         // $message->getHeaders()->addTextHeader('x-ses-configuration-set', 'HandleNotification')->addTextHeader('unique-id', Str::random(5));
//             });

//     echo "<pre>";print_r($test);exit;
    // return redirect()->to('/admin');
   return view('welcome');
});

// Route::get('/', function () {
//     echo DB::table('customers')->where('segment_id',8)->count();
//     DB::table('jobs')->truncate();
//     DB::table('job_batches')->truncate();exit;
//     exit;
//     return redirect()->to('/admin');
//    return view('welcome');
// });

// Route::get('/sendEmail', [EmailController::class, 'sendEmail']);
// Route::get('/sendRetargetting', [EmailController::class, 'emailRetargetting']);
// Route::get('/sendClickRetargetting', [EmailController::class, 'emailRetargettingOpen']);
// Route::get('/sendWhatsapp', [WhatsappController::class, 'sendWa']);
// Route::get('/sendSms', [SmsController::class, 'sendCampSms']);

