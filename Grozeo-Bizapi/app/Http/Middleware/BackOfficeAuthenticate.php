<?php

namespace App\Http\Middleware;

use Closure;
use BackOffice\Models\User;
use App\Http\Responses\ErrorResponse;

class BackOfficeAuthenticate
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
        $mobile = $request->header('Authorization');
        if (!is_null($mobile) && ($user = User::where('phone', $mobile)->first())) {
            $request->attributes->add(['authUser' => $user]);
            return $next($request);
        }
        return new ErrorResponse('invalid token');
    }
}
