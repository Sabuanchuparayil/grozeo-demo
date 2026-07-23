<?php

namespace BackOffice\Http\Controllers\Drivers;

use BackOffice\Http\Requests\Drivers\DriveUpdateStatusRequest;
use BackOffice\Http\Repositories\Drivers\DriverStatusUpdateRepository;

class DriverStatusUpdateController
{
    protected $authRepo;

    public function __construct(DriverStatusUpdateRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }
    
    /**
     * driver update online status to 0 or 1
     *
     * @return array
     */
    public function driverUpdateOnlineStatus(DriveUpdateStatusRequest $request)
    {
        return $this->authRepo->driverUpdateOnlineStatus($request->validated());
    }
}