<?php

namespace App\Http\Middleware;

use Closure;
use BackOffice\Models\User;
use App\Http\Responses\ErrorResponse;

class OptionCors
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
        return $request->getMethod() === 'OPTIONS'
            ? $this->addCorsHeaders()
            : $next($request);
    }

    public function addCorsHeaders()
    {
        return response('ok')->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Origin', implode(',', config('cors.default_profile.allow_origins')))
            ->header('Access-Control-Allow-Methods', implode(',', config('cors.default_profile.allow_methods')))
            ->header('Access-Control-Allow-Headers', implode(',', config('cors.default_profile.allow_headers')));
        }
}
