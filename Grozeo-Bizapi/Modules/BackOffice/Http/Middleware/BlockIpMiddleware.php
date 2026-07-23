<?php
namespace BackOffice\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class BlockIpMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $whiteList = config('blockedips.iplist');
        if(!in_array($request->ip(), $whiteList))
        {
            abort(403, "You are restricted to access the site.");
        }
        return $next($request);
    }
}