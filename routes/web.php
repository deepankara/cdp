<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use Jenssegers\Agent\Agent;


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
    $agent = new Agent();
    $agent->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/126.0.6478.153 Mobile/15E148 Safari/604.1');
    $device = $agent->device();
    $platform = $agent->platform();
    $browser = $agent->browser();
    echo "<pre>";print_r($device);
    echo "<pre>";print_r($platform);
    echo "<pre>";print_r($browser);exit;
    return view('welcome');
});

Route::get('/sendEmail', [EmailController::class, 'sendEmail']);
Route::get('/sendRetargetting', [EmailController::class, 'emailRetargetting']);
Route::get('/sendClickRetargetting', [EmailController::class, 'emailRetargettingOpen']);

