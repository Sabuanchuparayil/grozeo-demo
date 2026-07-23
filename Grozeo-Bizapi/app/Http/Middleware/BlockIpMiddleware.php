<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;


class BlockIpMiddleware
{
    protected $whiteList = ['3.111.72.168'];

    public function handle(Request $request, Closure $next)
    {
        if(!in_array($request->ip(), $this->whiteList))
        {
            abort(403, "You are restricted to access the site.");
        }
        return $next($request);
    }
}