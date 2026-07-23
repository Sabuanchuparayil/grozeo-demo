<?php

namespace App\Status;

use Exception;

final class TransferRequestStatus
{
    //1:Created,2:Order Processing, 5:Order Completed, 10: Order Deleted
    public const TRANSFER_REQUESTED = 1;

    public const TRANSFER_PARTIALY_ATTENDED = 5;
    
    public const TRANSFER_COMPLETLY_ATTENDED = 10;

    public const TRANSFER_INVOKE_EXPIRED = 15;

    //0:Transfer Requested/1:Transfer Invoked/2:Stock Requested/3:Sales ORDER
    public const TRANSFER_MANUAL_REQUESTED = 0;

    public const TRANSFER_INVOKED = 1;

    public const TRANSFER_STOCK_REQUESTED = 2;

    public const TRANSFER_SALES_ORDER = 3;

    public const TRANSFER_DETAILS_REQUESTED = 1;
    
    public const ORDER_PICKER_POLLED = 4;
    
    public const POLL_NO_RESPONSE = 6;
    
    public const ASSIGNED_ORDER_PICKER = 7;
    
    public const MANUALLY_ASSIGNED_ORDER_PICKER = 8;
    
    public const POLL_REJECTED = 9;

    public const TRANSFER_ORDER_COMPLETED = 20;

    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
