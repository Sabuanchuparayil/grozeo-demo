<?php

namespace BackOffice\Http\Controllers\Drivers;

use BackOffice\Http\Repositories\Drivers\DriverOrderRepository;

class DriverOrderController
{
    protected $driverOrderRepo;

    public function __construct(DriverOrderRepository $driverOrderRepo)
    {
        $this->driverOrderRepo = $driverOrderRepo;
    }

    /**
     * driver get driver details
     *
     * @return array
     */
    public function pendingOrderDetails()
    {
        return $this->driverOrderRepo->pendingOrderDetails();
    }
}