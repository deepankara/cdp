<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


class AuthController extends BaseController
{
    public function login(Request $request) {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->otp])) {
            $user = Auth::user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->expires_at = Carbon::now()->addWeeks(1); // Set token expiration (example: 1 week)
            $token->save();

            $data = [
                'access_token' => $tokenResult->accessToken, // Acce    ss token value
                'token_type' => 'Bearer', // Token type (Bearer for OAuth2)
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(), // Token expiration time
                'refresh_token' => $token->refresh_token, // Refresh token value,
                'email' => Auth::user()->email,
                'name' => Auth::user()->name,
                'linkedin_profile_url'=>Auth::user()->linkedin_profile,
                'campaign_id'=>Auth::user()->referal_campaign_id,
                'show_referal'=>Auth::user()->is_show_referal
            ];
            return $this->sendResponse($data, 'User login successfully.');
        }
        else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],401);
        }
    }

    public function getToken(Request $request){
        if(isset($request->header()['php-auth-user']) && $request->header()['php-auth-user'] != '' && isset($request->header()['php-auth-pw']) && $request->header()['php-auth-pw'] != ''){
            $email = $request->header()['php-auth-user'];
            $password = $request->header()['php-auth-pw'][0];
            if(Auth::attempt(['email' => $email, 'password' => $password])) {
                $user = Auth::user();
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();

                $data = [
                    'access_token' => $tokenResult->accessToken, // Acce    ss token value
                    'token_type' => 'Bearer', // Token type (Bearer for OAuth2)
                    'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString() 
                ];
                return $this->sendResponse($data, 'Token Generated Successfully');
            }else {
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],401);
            }
        }
    }
}
