<?php

namespace App\Status;

use Exception;

final class B2bOrderStatus
{
    public const ORDER_CREATED = 1;
 
    public const ORDER_PICKER_POLLED = 3;

    public const POLL_NO_RESPONSE = 5;

    public const ASSIGNED_ORDER_PICKER = 6;

    public const MANUALLY_ASSIGNED_ORDER_PICKER = 7;

    public const MANUAL_ASSIGNMENT = 7;
    
    public const POLL_REJECTED = 4;

    public const READY_FOR_INVOICE = 8;

    public const READY_FOR_DELIVERY = 8;

    public const ASSIGNED_GODOWN_BOY = 6;

    public const GODOWN_BOY_POLLED = 3;

    public const INSUFFICIENT_ITEMS = 17;

    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
