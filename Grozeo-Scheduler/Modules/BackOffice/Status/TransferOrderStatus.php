<?php

namespace BackOffice\Status;

use Exception;

final class TransferOrderStatus
{
    public const CREATED = 1;

    public const GODOWN_BOY_POLLED = 2;

    public const POLL_NO_RESPONSE = 3;

    public const ASSIGNED_GODOWN_BOY = 4;

    public const PARTIAL_PACKING = 5;

    public const TO_MANUALLY_ASSIGN = 6; 

    public const POLL_REJECTED = 7;    

    public const PICKER_APPROVED = 8;    

    public const COMPLETED = 10;

    public const CANCELLED = 15;

    public const CANCELLED_AFTER_PACKING = 16;

    public const INCOMPLETE_ORDER = 9;
    
    public const SCHEDULED_ORDER = 11;

    public const HOLD_FOR_CUSTOMER_APPROVAL = 20;

    public const PACKED_NOT_BOXED = 12;

    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
