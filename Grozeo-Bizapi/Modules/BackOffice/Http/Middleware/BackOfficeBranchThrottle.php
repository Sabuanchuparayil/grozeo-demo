<?php
namespace BackOffice\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class BackOfficeBranchThrottle extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        $auths = @$request->header('Authorization') ? '|'.$request->header('Authorization') : "";
        if ($user = $request->user())
        {
            return sha1($user->getAuthIdentifier());
        }
        if ($route = $request->route())
        {
            return sha1($route->getDomain().'|'.$request->ip().$auths);
        }
        throw new RuntimeException('Unable to generate the request signature. Route unavailable.');
    }
}