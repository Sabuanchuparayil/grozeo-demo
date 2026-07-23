<?php

namespace App\Http\Repositories\SocialLogins;

use Socialite;
use App\Helpers\HttpCurlCalls;

class InstagramLogin
{
    public function __construct() {}

    
    public function socLogin($request)
    {
        $code = $request['code'];
        if (!$code)
        {
            return ['error' => 'No code provided'];
        }
        // Exchange code for access token
        $settings = config('socials.instagram.decrypt.settings');
        $settings['code'] = $code;
        $settings['redirect_uri'] = url("api/signup/socials/login/request/instagram");
        $authTokenResp = (new HttpCurlCalls)->curlCall(config('socials.instagram.decrypt.url'), json_encode($settings), 'POST', ['Content-Type: application/json']);
        if (@$authTokenResp->error || !isset($authTokenResp->access_token))
        {
            return ['error' => 'Failed to exchange code for token'];
        }
        $accessToken = $authTokenResp->access_token;

        $response = (new HttpCurlCalls)->curlCall(config('socials.instagram.userinfo.url'), [], 'GET', ['Content-Type: application/json', 'Authorization: Bearer '.$accessToken]);

        if(@$response->error || !isset($response->email))
        {
            return ['error' => 'Failed to get user info'];
        }

        return [
            'email' => $response->email,
            'name'  => $response->name,
            'error' => ''
        ];
    }
}