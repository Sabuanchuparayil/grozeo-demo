<?php
namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Models\AppConfig;

class AuthGuestMiddleware extends BaseMiddleware
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
        try {
                $error=false;
                $payload=JWTAuth::getPayload(JWTAuth::parseToken())->toArray();
                if(isset($payload['dummy'])){
                    if(str_contains($request->path(),'cart/')){
                        if($request->header("R-Guest-Data"))
                        {
                            return $next($request);
                        }
                        $error=1;
                    }else{
                        $user= AppConfig::where('brac_id', $payload["sub"])->first();
                        $user["cust_id"]=-1;
                        
                    }

                }else{
                    $user = JWTAuth::parseToken()->authenticate();  
                    if($user){
                        if(isset($payload["aud"]) && ($payload["aud"] != ~getUserAgentType())){
                                $error=true;
                            
                        }
                    }else{
                        $error=true;
                    }
                }
                if($error){
                    return response()->json([
                            'status' => 'error',
                            'error' => [
                                'msg' => 'Token is Invalid'
                            ]
                        ],401);
                    }else{
                    if(isset($user)){
                        if($user->cust_id =="-1"){
                            $user->cust_branch_id=0; //getBranchIdForll();  
                        }
                        // $request->attributes->add(['authUser' => $user]);
                        $request->attributes->add(['authUser' => $user]);
                    }
                }
    
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $msg= 'Token is Invalid';
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $msg= 'Token is Expired';
            }else{
                $msg= 'Authorization Token not found';
            }
            return response()->json([
                'status' => 'error',
                'error' => [
                    'msg' => $msg
                ]
            ],401);


        }
        return $next($request);
    }
}

?>
