<?php

namespace BackOffice\Status;

use Exception;

final class QugeoType
{
    //1- Drive, 2-Hired, 3-CustomerPickup ,4-Courier, 5-DriverPickup, 6-ManualDelivery
    public const DRIVE = 1;

    public const HIRED = 2;

    public const CUSTOMER_PICKUP = 3;

    public const COURIER = 4;

    public const DRIVER_PICKUP = 5;

    public const MANUAL_DELIVERY = 6;

    private function __construct()
    {
        throw new Exception('Cannot instantiate QugeoType');
    }
}
