<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Arr;

use App\Exceptions\ErrException;
use Illuminate\Support\Facades\Log;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \App\Exceptions\MsgException::class,
        \App\Exceptions\OfferException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
       // dd($exception);
        $statusCode = 400;
        $msg = $exception->getMessage();
       // dd($exception);
        switch(true) {
            case $exception instanceof ModelNotFoundException :
                $msg = $msg . ' -- No results found';
                break;
            //case $exception instanceof AuthorizationException :
            case $exception instanceof AuthenticationException :
                $msg = 'Unauthorized';
                $statusCode = 401;
                break;
            case $exception instanceof NotFoundHttpException :
                $msg = 'Invalid url';
                $statusCode = 404;
                break;
            case $exception instanceof MsgException :
                $statusCode = 406;
                break;
            case $exception instanceof ErrException :
                $statusCode = 400;
                break;
            case $exception instanceof ValidationException:
                // $msg = $exception->getMessage();//array_collapse($exception->errors())[0];
                $msg = Arr::collapse($exception->errors());
            break;

            default :
                break;
        }
        if($msg=="Token has expired")
        {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'msg' => $msg,
                    'code'=>"401",
                ]
            ], 401);
        }
        else{
            return response()->json([
                'status' => 'error',
                'error' => [
                    'msg' => $msg,
                    // 'line' => $exception->getLine(),
                    // 'file' => $exception->getFile()
                ]
            ],$statusCode);
        }

        // return parent::render($request, $exception);
    }
}
