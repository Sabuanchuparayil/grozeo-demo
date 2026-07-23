<?php

namespace App\Http\Repositories\SocialLogins;

use Socialite;
use App\Helpers\HttpCurlCalls;

class FacebookLogin
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
        $settings = config('socials.facebook.decrypt.settings');
        $settings['code'] = $code;
        $settings['redirect_uri'] = url("api/signup/socials/login/request/facebook");
        $authTokenResp = (new HttpCurlCalls)->curlCall(config('socials.facebook.decrypt.url'), json_encode($settings), 'POST', ['Content-Type: application/json']);
        if (@$authTokenResp->error || !isset($authTokenResp->access_token))
        {
            return ['error' => 'Failed to exchange code for token'];
        }
        $accessToken = $authTokenResp->access_token;

        $url = config('socials.facebook.userinfo.url').$accessToken;
        $response = (new HttpCurlCalls)->curlCall($url, [], 'GET', []);
        if(@$response->error || !isset($response->email))
        {
            return ['error' => 'Failed to get user info'];
        }

        return [
            'email' => $response->email,
            'name'  => "{@$response->first_name} {@$response->last_name}",
            'error' => ''
        ];

    }
}