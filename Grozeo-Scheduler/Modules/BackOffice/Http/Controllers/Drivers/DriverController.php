<?php

namespace BackOffice\Http\Controllers\Drivers;

use BackOffice\Http\Repositories\Drivers\DriverRepository;

class DriverController
{
    protected $driverRepo;

    public function __construct(DriverRepository $driverRepo)
    {
        $this->driverRepo = $driverRepo;
    }

    /**
     * driver get driver details
     *
     * @return array
     */
    public function driverDetails()
    {
        return $this->driverRepo->driverDetails();
    }
}