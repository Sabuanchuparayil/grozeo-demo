<?php

namespace App\Http\Controllers\Driver\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\OtpRequest;
use App\Http\Requests\Driver\OtpVerifyRequest;
use Illuminate\Http\Request;
use App\Http\Responses\{
    SuccessResponse,
    SuccessWithData,
    ErrorResponse
};
use App\Http\Resources\OtpResource;
use App\Http\Repositories\Driver\OtpRepository;

class OtpController extends Controller
{
    protected $otpRepo;

    public function __construct(OtpRepository $otpRepo)
    {
        $this->otpRepo = $otpRepo;
    }
    
    /**
     * Fetch driver details based on mobile number
     * old api. to be removed
    */
    public function sendOTP(OtpRequest $request)
    {
        // Fetch driver details from repository
        $otpDetails = $this->otpRepo->sendOTP($request->mobile_number);
        
         // Return formatted response
         if ($otpDetails) {
            return new OtpResource($otpDetails);
            }
            return new ErrorResponse("Driver not found");
    }
    /**
     * old api. to be removed
    */
    public function verifyOTP(OtpVerifyRequest $request)
    {

        $otpDetails = $this->otpRepo->verifyOTP($request);

        return $otpDetails;

    }

    /**
     * Send OTP API
    */
    public function otpSend(OtpRequest $request)
    {
        // Fetch driver details from repository
        $otpDetails = $this->otpRepo->otpSend($request);
        return $otpDetails;
    }
    /**
     * Verify OTP API
    */
    public function otpVerify(OtpVerifyRequest $request)
    {
        $otpDetails = $this->otpRepo->otpVerify($request);
        return $otpDetails;
    }
}
