<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class ErrorWithData implements Responsable
{
    protected $msg;

    protected $data;

    protected $code;

    public function __construct($msg = "Error", $data, $code = 400)
    {
        $this->msg = $msg;
        $this->data = $data;
        $this->code = $code;
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
                    'data' => $this->data,
                ]
            ], $this->code);
    }

}
