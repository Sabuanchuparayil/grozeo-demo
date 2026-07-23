<?php

namespace App\Status;

use Exception;

final class CpdOrderStatus
{
    public const CREATED = 0;

    public const MANUAL_QUEUED = 1;
    
    public const GODOWN_BOY_POLLED = 2;

    public const ASSIGNED_GODOWN_BOY = 3;

    public const SCANNING_STARTED = 4;

    public const INCOMPLETE_ORDER = 5;

    public const ORDER_COMPLETED = 6;

    public const CANCELLED = 7;

    public const EXPIRED = 8;

    public const DISPATCHED = 9;

    public const PARTLY_RECEIVED = 10;

    public const RECEIVED = 11;

    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
