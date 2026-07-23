<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Config;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Log;
use App\Models\Drivers\QugeoDriver;

class DriverJwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try
        {
            $token = JWTAuth::getToken();
            $rawToken = $token ? $token->get() : null;
            $payload = JWTAuth::getPayload(JWTAuth::parseToken());
            
            $whereCondition = [
                ['d_ID', @$payload["sub"]],
                ['d_Active', 1]
            ];
            if($rawToken)
            {
                $whereCondition[] = ['d_apikey', $rawToken];
            }
            $user= QugeoDriver::where($whereCondition)->first();
            $error=false;
            if($user)
            {
                if(isset($payload["aud"]) && ($payload["aud"] != getUserAgentType()))
                {
                    //  $error=true;
                }
            }
            else
            {
                $error=true;
            }
            if($error)
            {
                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'msg' => 'Token is Invalid'
                    ]
                ], 401);
            }
            else
            {
                $request->attributes->add(['authUser' => $user]);
            }
        }
        catch (Exception $e)
        {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException)
            {
                $msg= 'Token is Invalid';
            }
            else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException)
            {
                $msg= 'Token is Expired';
            }
            else
            {
                $msg= 'Authorization Token not found';
            }
            return response()->json([
                'status' => 'error',
                'error' => [
                    'msg' => $msg
                ]
            ], 401);
        }
        return $next($request);
    }
}