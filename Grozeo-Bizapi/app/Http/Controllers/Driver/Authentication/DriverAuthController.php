<?php

namespace App\Http\Controllers\Driver\Authentication;

use App\Http\Requests\Driver\DriverAuthRequest;
use App\Http\Repositories\Driver\DriverAuthRepository;

class DriverAuthController
{
    protected $authRepo;

    public function __construct(DriverAuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }
    
    /**
     * 
     * driver authentication
     *
     * @return array
     */
    public function driverAuthentication(DriverAuthRequest $request)
    {
        return $this->authRepo->driverAuthentication($request->validated());
    }
    
    
}