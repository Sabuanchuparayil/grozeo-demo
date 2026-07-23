<?php
namespace App\Http\Controllers\SocialLogins;

Use Redirect;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repositories\SocialLogins\SocialLoginRepository;

class SocialLoginController extends Controller
{
    public function __construct(SocialLoginRepository $socLogin)
    {
        $this->socLogin = $socLogin;
    }

    public function socialLogins($type, Request $request)
    {
        return $this->socLogin->socialLogins($type, $request);
    }
    public function socialLoginRequest($type, Request $request)
    {
        try
        {
            $code = $request->query('code');
            $state = $request->query('state');
            $attach = config("socials.{$type}.auth.redirect_att");
            $redirectURL = "{$state}{$attach}?code=$code";
            return Redirect::away($redirectURL);

        }
        catch (\Exception $e)
        {
            $response = new \stdClass();
            $response->error = $e->getMessage();
            return view('sociallogin.social-login-error', compact('response'));
        }
    }

    public function redirectToLogin($type, Request $request)
    {
        try
        {
            $socDetails = config("socials.{$type}");
            if($socDetails)
            {
                $urlVars = [
                    '{#REDIRECT_URL}'   => url("api/signup/socials/login/request/{$type}"),
                    '{#CLIENT_ID}'      => $socDetails['decrypt']['settings']['client_id'],
                    '{#STATE}'          => $request->query('redirect')
                ];
                $url = strtr($socDetails['auth']['url'], $urlVars);
                $response = new \stdClass();
                $response->url = $url;
                return view('sociallogin.social-login', compact('response'));
            }
            $response = new \stdClass();
            $response->error = 'Login not available';
            return view('sociallogin.social-login-error', compact('response'));
        }
        catch (\Exception $e)
        {
            $response = new \stdClass();
            $response->error = $e->getMessage();
            return view('sociallogin.social-login-error', compact('response'));
        }
    }
}