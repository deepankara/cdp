<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
}
