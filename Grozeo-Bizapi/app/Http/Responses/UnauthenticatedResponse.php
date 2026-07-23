<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class UnauthenticatedResponse implements Responsable
{
    protected $msg;

    public function __construct($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json([
            'status' => 'error',
            'default_currency' => config('app.def_currency_symbol'),
            'error' => [
                'msg' => $this->msg,
            ]
        ], 401);
    }
    
}
