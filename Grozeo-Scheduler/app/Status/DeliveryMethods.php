<?php

namespace App\Status;

use Exception;

final class DeliveryMethods
{
    //0 - NoDelivery,1 - Drive,2 - Hired, 4 - CustomerPickup,8 - Courier, 16 - DriverPickup,32 - Manual Delivery
    public const NODELIVERY = 0;

    public const DRIVE = 1;

    public const HIRED = 2;

    public const CUSTOMER_PICKUP = 4;

    public const COURIER = 8;

    public const DRIVER_PICKUP = 16;

    public const MANUAL_DELIVERY = 32; 

    private function __construct()
    {
        throw new Exception('Cannot instantiate Deliverymethods');
    }

}
