<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

class SuccessWithData implements Responsable
{
    protected $data;

    protected $code;

    public function __construct($data, $code = 200)
    {
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
            'status' => 'ok',
            'default_currency' => config('app.def_currency_symbol'),
            'data' => $this->data,
        ], $this->code);
    }
    
}
