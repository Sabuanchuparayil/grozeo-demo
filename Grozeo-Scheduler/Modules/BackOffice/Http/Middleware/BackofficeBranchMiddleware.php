<?php

    namespace BackOffice\Http\Middleware;

    use Closure;
    use JWTAuth;
    use Exception;
    use Config;
    use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
    use Illuminate\Support\Facades\Log;
    use BackOffice\Models\Branch;
    use App\Http\Responses\UnauthenticatedResponse;

    class BackofficeBranchMiddleware extends BaseMiddleware
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
              $br_ReferenceID = $request->header('Authorization');
              $branch = Branch::where('br_ReferenceID', $br_ReferenceID)->first();
              if(!$branch) {
                  return new UnauthenticatedResponse('Invalid Authentication');
              }
              $request->attributes->add(['authUser' => $branch]);
              return $next($request);
            
        }
    }

?>