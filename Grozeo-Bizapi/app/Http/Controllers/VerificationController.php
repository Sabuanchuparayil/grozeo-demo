<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Requests\Verify\OtpRequest;
use App\Http\Repositories\VerificationRepository;
use App\Http\Requests\Verify\VerificationRequest;
use App\Http\Requests\Verify\VerifyPasswordRequest;
//use App\Http\Requests\Verify\KalyeraRequest;

class VerificationController extends Controller
{
    //
    public function __construct(VerificationRepository $verifyLog)
    {
        $this->verifyLog = $verifyLog;
    }
    
    public function store(OtpRequest $request, $type='mobile')
    {
        switch ($type)
        {
            case 'mobile':
                $msg = new SuccessResponse($this->verifyLog->store($request->validated()));
                break;
            case 'email':
                $msg = new SuccessResponse($this->verifyLog->storeEmail($request->validated()));
                break;
            
            default:
                $msg = new ErrorResponse("Not available");
                break;
        }
        return $msg;
    }

    public function verify(VerificationRequest $request, $type='mobile')
    {
        switch ($type)
        {
            case 'mobile':
                $msg = $this->verifyLog->verify($request->validated());
                break;
            case 'email':
                $msg = $this->verifyLog->verifyEmail($request->validated());
                break;
            
            default:
                $msg = new ErrorResponse("Not available");
                break;
        }
        return $msg;
    }
    public function verifyPassword(VerifyPasswordRequest $request)
    {
        return $this->verifyLog->verifyPassword($request->validated());
    }

    public function impuser(VerificationRequest $request){
        return $this->verifyLog->impuser($request->validated());
    }
//    public function incomingcall(KalyeraRequest $request){
//        return $this->verifyLog->kalyeraIncomingCalls($request->validated());
//    }

}
