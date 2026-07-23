<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\Pincode\GetPinRequest;
use App\Http\Repositories\Pincode\PincodeRepository;

class PincodeController extends Controller
{
    protected $pincode;

    public function __construct(PincodeRepository $pincode)
    {
        $this->pincode = $pincode;
    }

    public function get(GetPinRequest $request)
    {
        return new SuccessWithData(
            $this->pincode->get($request->validated())
        );
    }    
}
