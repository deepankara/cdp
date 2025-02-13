<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\EmailController;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;
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

// Route::get('/', function () {
//     return redirect()->to('/admin');
//    return view('welcome');
// });

Route::get('/sendEmail', [EmailController::class, 'sendEmail']);
Route::get('/sendRetargetting', [EmailController::class, 'emailRetargetting']);
Route::get('/sendClickRetargetting', [EmailController::class, 'emailRetargettingOpen']);
Route::get('/sendWhatsapp', [EmailController::class, 'sendWa']);
Route::get('/sendSms', [EmailController::class, 'sendCampSms']);

