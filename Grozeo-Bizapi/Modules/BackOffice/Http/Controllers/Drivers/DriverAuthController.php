<?php

namespace BackOffice\Http\Controllers\Drivers;

use BackOffice\Http\Requests\Drivers\{
    DriverAuthRequest,
    DriverOtpVerifyRequest
};
use BackOffice\Http\Repositories\Drivers\DriverAuthRepository;

class DriverAuthController
{
    protected $authRepo;

    public function __construct(DriverAuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }
    
    /**
     * driver authentication
     *
     * @return array
     */
    public function driverAuthentication(DriverAuthRequest $request)
    {
        return $this->authRepo->driverAuthentication($request->validated());
    }
    
    /**
     * driver otp verification
     *
     * @return array
     */
    public function driverOtpVerification(DriverOtpVerifyRequest $request)
    {
        return $this->authRepo->driverOtpVerification($request->validated());
    }
}