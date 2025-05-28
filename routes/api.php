<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\WebPushController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/token',[AuthController::class,'getToken'])->name('getToken');


Route::middleware('auth:api')->group(function () {
    Route::post('/send-message',[ApiController::class,'sendMessage'])->name('sendMessage');
    Route::post('/create-push-notifications',[WebPushController::class,'createPushNotifcation'])->name('createPushNotifcation');
});

Route::post('/email-webhook', [EmailController::class, 'emailWebhook']);
Route::post('/telSpiel-email-webhook', [EmailController::class, 'telSpielEmailWebhook']);
Route::any('/whatsapp-webhook', [WhatsappController::class, 'whatsappWebook']);
Route::any('/sms-webhook', [SmsController::class, 'smsWebhook']);

