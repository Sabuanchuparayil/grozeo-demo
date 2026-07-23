<?php

namespace App\Status;

use Exception;

final class CustomerOrderStatus
{
    public const PAYMENT_INITIATED = 1;
    
    public const PAYMENT_FAILED = 2;

    public const MANUAL_ORDER_PAYMENT = 3;

    public const SUCCESS = 4;

    public const GODOWN_BOY_POLLED = 5;

    public const ASSIGNED_GODOWN_BOY = 6;

    public const MANUAL_ASSIGNMENT = 7;

    public const DELIVERY_CARTING = 8;

    public const READY_FOR_DELIVERY = 9;

    public const INSUFFICIENT_ITEMS = 23;

    public const POLL_REJECTED = 11;

    public const POLL_NO_RESPONSE = 12;

    public const DELIVERY_MANUAL_SCHEDULE = 13;

    public const CANCELLED = 19;

    public const CHECKEDOUT = 0;

    public const ON_HOLD=22;
    public const PACK_ON_HOLD=25;
    public const CPR_ON_HOLD=27;
    
    public const DARK_ON_HOLD=40;
    public const HOLD_FOR_CUSTOMER_APPROVAL=50;

    public const REFUND_INITIATED = 56;
    public const REFUND_COMPLETED = 52;
    public const REFUND_CANCELLED = 53;

    public const DELIVERED = 18;
    public const PICKUP_CONSIGNMENT = 20;
    public const DELIVERY_ASSIGNED = 14;

    public const PAYMENT_TIMEDOUT = 21;
    
    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
